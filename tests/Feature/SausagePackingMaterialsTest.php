<?php

namespace Tests\Feature;

use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SausagePackingMaterialsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'recipes.draft_table' => 'recipe_data_draft',
            'production_orders.recipe_table' => 'other_recipe_table',
        ]);

        if (getenv('PACKING_TEST_SQLSERVER') === '1') {
            config(['database.connections.sqlsrv.prefix' => '#']);
            DB::purge('sqlsrv');
            DB::setDefaultConnection('sqlsrv');
            DB::connection('sqlsrv')->setTablePrefix('#');
        } else {
            config([
                'database.connections.sqlite.database' => ':memory:',
                'database.connections.sqlite.url' => null,
            ]);
            DB::purge('sqlite');
            DB::setDefaultConnection('sqlite');
        }

        if (getenv('PACKING_TEST_SQLSERVER') === '1') {
            $this->createSqlServerTemporaryTables();
        } else {
            $this->createSqliteTables();
        }

        $user = new User();
        $user->id = 1;
        $this->actingAs($user)->withoutMiddleware();
    }

    private function createSqlServerTemporaryTables(): void
    {
        DB::unprepared('CREATE TABLE #idt_transfers (
            id int IDENTITY(1,1) PRIMARY KEY,
            filter1 nvarchar(255) NOT NULL, product_code nvarchar(255) NOT NULL,
            location_code nvarchar(255) NULL, chiller_code nvarchar(255) NULL,
            total_crates int NULL, full_crates int NULL, incomplete_crate_pieces int NULL,
            total_pieces int NULL, total_weight decimal(18,5) NOT NULL,
            transfer_type nvarchar(255) NULL, transfer_from nvarchar(255) NOT NULL,
            description nvarchar(255) NULL, order_no nvarchar(255) NULL,
            batch_no nvarchar(255) NOT NULL, user_id int NOT NULL,
            batch_linked bit NOT NULL DEFAULT 0, created_at datetime NULL, updated_at datetime NULL
        )');
        DB::unprepared('CREATE TABLE #generated_production_orders (
            id int NOT NULL, production_order_no nvarchar(255) NOT NULL,
            line_no int NOT NULL, item_no nvarchar(255) NOT NULL, quantity decimal(18,5) NOT NULL,
            process nvarchar(255) NOT NULL, recipe nvarchar(255) NULL,
            line_type nvarchar(255) NOT NULL, batch_no nvarchar(255) NOT NULL,
            published bit NOT NULL DEFAULT 0, batch_linked bit NOT NULL DEFAULT 0,
            created_at datetime NULL, updated_at datetime NULL
        )');
        DB::unprepared('CREATE TABLE #recipe_data_draft (
            id int IDENTITY(1,1) PRIMARY KEY, process nvarchar(255) NOT NULL,
            output_item nvarchar(255) NOT NULL, recipe nvarchar(255) NOT NULL,
            input_item nvarchar(255) NOT NULL, input_item_qt_per float NOT NULL
        )');
    }

    private function createSqliteTables(): void
    {
        Schema::create('idt_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('filter1');
            $table->string('product_code');
            $table->string('location_code')->nullable();
            $table->string('chiller_code')->nullable();
            $table->integer('total_crates')->nullable();
            $table->integer('full_crates')->nullable();
            $table->integer('incomplete_crate_pieces')->nullable();
            $table->integer('total_pieces')->nullable();
            $table->decimal('total_weight', 18, 5);
            $table->string('transfer_type')->nullable();
            $table->string('transfer_from');
            $table->string('description')->nullable();
            $table->string('order_no')->nullable();
            $table->string('batch_no');
            $table->integer('user_id');
            $table->boolean('batch_linked')->default(false);
            $table->timestamps();
        });

        Schema::create('generated_production_orders', function (Blueprint $table) {
            $table->integer('id');
            $table->string('production_order_no');
            $table->integer('line_no');
            $table->string('item_no');
            $table->decimal('quantity', 18, 5);
            $table->string('process');
            $table->string('recipe')->nullable();
            $table->string('line_type');
            $table->string('batch_no');
            $table->boolean('published')->default(false);
            $table->boolean('batch_linked')->default(false);
            $table->timestamps();
        });

        Schema::create('recipe_data_draft', function (Blueprint $table) {
            $table->increments('id');
            $table->string('process');
            $table->string('output_item');
            $table->string('recipe');
            $table->string('input_item');
            $table->float('input_item_qt_per');
        });
    }

    private function orderLine($id, $order, $item, $quantity, $type = 'consumption', $overrides = []): void
    {
        DB::table('generated_production_orders')->insert(array_merge([
            'id' => $id,
            'production_order_no' => $order,
            'line_no' => $type === 'output' ? 1000 : $id * 1000,
            'item_no' => $item,
            'quantity' => $quantity,
            'process' => 'Packing',
            'recipe' => '1240W47',
            'line_type' => $type,
            'batch_no' => '11L01',
        ], $overrides));
    }

    private function recipeLine($item, $quantity, $overrides = []): void
    {
        DB::table('recipe_data_draft')->insert(array_merge([
            'process' => 'Packing',
            'output_item' => 'J31019129',
            'recipe' => '1240W47',
            'input_item' => $item,
            'input_item_qt_per' => $quantity,
        ], $overrides));
    }

    private function saveTransfer($weight = 24.48): void
    {
        $this->from('/sausage/create-idt')->post('/save/idt', [
            'crates_valid' => '1',
            'product' => 'J31019129',
            'weight' => $weight,
            'batch' => '11L',
            'batch_no' => '01',
        ])->assertRedirect('/sausage/create-idt');
    }

    public function test_it_updates_only_materials_on_the_selected_order_using_the_new_weight_and_full_recipe_values(): void
    {
        $this->orderLine(1, 'ORDER-1', 'J31019129', 24.47802, 'output');
        $this->orderLine(2, 'ORDER-1', 'G4062', 25.70192);
        $this->orderLine(3, 'ORDER-1', 'H123036', 4.56199);
        $this->orderLine(4, 'ORDER-1', 'H223041', 0.11466);
        $this->orderLine(5, 'ORDER-1', 'H223030', 0.11466);
        $this->orderLine(6, 'ORDER-2', 'H123036', 9);
        $this->orderLine(7, 'ORDER-1', 'H123036', 8, 'consumption', ['batch_no' => 'OTHER']);
        $this->orderLine(8, 'ORDER-1', 'H123036', 7, 'consumption', ['process' => 'Stuffing']);
        $this->orderLine(9, 'ORDER-2', 'J31019129', 12, 'output');
        $this->recipeLine('G4062', 105);
        $this->recipeLine('H123036', 18.6371);
        $this->recipeLine('H223041', 0.4684);
        $this->recipeLine('H223030', 0.4684);
        $this->recipeLine('G9999', 999, ['recipe' => 'OTHER']);
        $this->recipeLine('G9999', 999, ['process' => 'Stuffing']);

        Toastr::shouldReceive('success')->once();
        $this->saveTransfer();

        $lines = DB::table('generated_production_orders')->orderBy('id')->get()->keyBy('id');
        $this->assertEquals(24.48, $lines[1]->quantity);
        $this->assertEquals(1, $lines[1]->batch_linked);
        $this->assertEquals(25.70192, $lines[2]->quantity);
        $this->assertEquals(round(24.48 * 18.6371 / 105, 5), $lines[3]->quantity);
        $this->assertEquals(round(24.48 * 0.4684 / 105, 5), $lines[4]->quantity);
        $this->assertEquals(round(24.48 * 0.4684 / 105, 5), $lines[5]->quantity);
        $this->assertEquals(9, $lines[6]->quantity);
        $this->assertEquals(8, $lines[7]->quantity);
        $this->assertEquals(7, $lines[8]->quantity);
        $this->assertEquals(12, $lines[9]->quantity);
        $this->assertEquals(1, DB::table('idt_transfers')->first()->batch_linked);
    }

    public function test_material_calculation_reads_the_output_quantity_after_the_database_update(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::unprepared('ALTER TABLE #generated_production_orders ALTER COLUMN quantity decimal(18,2) NOT NULL');
        } else {
            DB::unprepared("CREATE TRIGGER round_output_quantity AFTER UPDATE OF quantity ON generated_production_orders
                WHEN NEW.line_type = 'output'
                BEGIN
                    UPDATE generated_production_orders SET quantity = round(NEW.quantity, 2) WHERE id = NEW.id;
                END");
        }

        $this->orderLine(1, 'ORDER-1', 'J31019129', 10, 'output');
        $this->orderLine(2, 'ORDER-1', 'H123036', 1);
        $this->recipeLine('G4062', 100);
        $this->recipeLine('H123036', 1000);
        Toastr::shouldReceive('success')->once();

        $this->saveTransfer(24.47802);

        $this->assertEquals(24.48, DB::table('generated_production_orders')->where('id', 1)->value('quantity'));
        $this->assertEquals(244.8, DB::table('generated_production_orders')->where('id', 2)->value('quantity'));
    }

    /**
     * @dataProvider invalidRecipeCases
     */
    public function test_invalid_recipes_roll_back_the_transfer_and_all_order_updates($base, $material, $extra): void
    {
        $this->orderLine(1, 'ORDER-1', 'J31019129', 24.47802, 'output');
        $this->orderLine(2, 'ORDER-1', 'H123036', 4.56199);
        if ($base !== null) {
            $this->recipeLine('G4062', $base);
        }
        if ($material !== null) {
            $this->recipeLine('H123036', $material);
        }
        if ($extra) {
            $this->recipeLine($extra[0], $extra[1]);
        }

        Toastr::shouldReceive('error')->once();
        $this->saveTransfer();

        $this->assertSame(0, DB::table('idt_transfers')->count());
        $this->assertEquals(24.47802, DB::table('generated_production_orders')->where('id', 1)->value('quantity'));
        $this->assertEquals(0, DB::table('generated_production_orders')->where('id', 1)->value('batch_linked'));
        $this->assertEquals(4.56199, DB::table('generated_production_orders')->where('id', 2)->value('quantity'));
    }

    public function invalidRecipeCases(): array
    {
        return [
            'missing base' => [null, 18.6371, null],
            'zero base' => [0, 18.6371, null],
            'multiple bases' => [105, 18.6371, ['G9999', 50]],
            'missing material' => [105, null, null],
            'conflicting material' => [105, 18.6371, ['H123036', 20]],
            'negative material' => [105, -1, null],
            'conflicting base' => [105, 18.6371, ['G4062', 106]],
        ];
    }

    public function test_it_skips_published_and_already_linked_outputs(): void
    {
        $this->orderLine(1, 'PUBLISHED', 'J31019129', 1, 'output', ['published' => true]);
        $this->orderLine(2, 'LINKED', 'J31019129', 2, 'output', ['batch_linked' => true]);
        $this->orderLine(3, 'ELIGIBLE', 'J31019129', 3, 'output');
        $this->orderLine(4, 'ELIGIBLE', 'H123036', 4);
        $this->recipeLine('G4062', 105);
        $this->recipeLine('H123036', 18.6371);
        $this->recipeLine('H123036', 18.6371);
        Toastr::shouldReceive('success')->once();

        $this->saveTransfer(0);

        $this->assertEquals(1, DB::table('generated_production_orders')->where('id', 1)->value('quantity'));
        $this->assertEquals(2, DB::table('generated_production_orders')->where('id', 2)->value('quantity'));
        $this->assertEquals(0, DB::table('generated_production_orders')->where('id', 3)->value('quantity'));
        $this->assertEquals(0, DB::table('generated_production_orders')->where('id', 4)->value('quantity'));
        $this->assertEquals(1, DB::table('idt_transfers')->first()->batch_linked);
    }

    public function test_published_material_rolls_back_the_transfer(): void
    {
        $this->orderLine(1, 'ORDER-1', 'J31019129', 24.47802, 'output');
        $this->orderLine(2, 'ORDER-1', 'H123036', 4.56199, 'consumption', ['published' => true]);
        Toastr::shouldReceive('error')->once();

        $this->saveTransfer();

        $this->assertSame(0, DB::table('idt_transfers')->count());
        $this->assertEquals(24.47802, DB::table('generated_production_orders')->where('id', 1)->value('quantity'));
    }

    public function test_no_matching_output_or_no_materials_preserves_existing_save_behavior(): void
    {
        Toastr::shouldReceive('success')->twice();
        $this->saveTransfer();
        $this->assertEquals(0, DB::table('idt_transfers')->first()->batch_linked);

        $this->orderLine(1, 'ORDER-1', 'J31019129', 24.47802, 'output');
        $this->saveTransfer();
        $this->assertEquals(24.48, DB::table('generated_production_orders')->where('id', 1)->value('quantity'));
        $this->assertEquals(1, DB::table('idt_transfers')->where('id', 2)->value('batch_linked'));
    }
}

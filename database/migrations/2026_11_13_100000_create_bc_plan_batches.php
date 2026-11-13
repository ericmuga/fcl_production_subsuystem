<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Released BC orders arrive in WMS cut into batches (BATCH = 80 kg): every WMS production order is one batch, linked
 * to the BC released order (plan_no) by batch_code. bc_sync_state keeps the incremental sync watermark.
 */
class CreateBcPlanBatches extends Migration
{
    public function up()
    {
        Schema::create('bc_plan_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 30)->unique();
            $table->string('plan_no', 20);
            $table->integer('plan_line_no');
            $table->integer('batch_no');
            $table->string('item_no', 20);
            $table->string('variant_code', 10)->nullable();
            $table->string('location_code', 10)->nullable();
            $table->date('due_date')->nullable();
            $table->string('batch_uom', 10)->nullable();
            $table->decimal('quantity', 18, 5)->default(0);          // in batch units (1 = full batch)
            $table->string('base_uom', 10)->nullable();
            $table->decimal('quantity_base', 18, 5)->default(0);     // kg
            $table->decimal('bc_executed_qty_base', 18, 5)->default(0);
            // BC side: sent | in_progress | completed | cancelled
            $table->string('bc_status', 12)->default('sent');
            // WMS side: pending | in_progress | completed | cancelled
            $table->string('wms_status', 12)->default('pending');
            $table->string('wms_order_no', 50)->nullable();           // WMS production order fulfilling the batch
            $table->dateTime('pushed_at')->nullable();
            $table->dateTime('bc_completed_at')->nullable();
            $table->dateTime('bc_modified_at')->nullable();
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();

            $table->index(['plan_no', 'plan_line_no', 'batch_no']);
            $table->index(['plan_no', 'wms_status']);
        });

        Schema::create('bc_sync_state', function (Blueprint $table) {
            $table->string('key', 50)->primary();
            $table->dateTime('watermark')->nullable();   // BC $systemModifiedAt (UTC) up to which changes were read
            $table->dateTime('last_run_at')->nullable();
            $table->text('last_stats')->nullable();
            $table->timestamps();
        });

        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->string('released_from', 20)->nullable();   // firm planned order the plan was released from
            $table->string('batch_uom', 10)->nullable();
        });

        Schema::table('generated_production_orders', function (Blueprint $table) {
            $table->integer('plan_line_no')->nullable();
            $table->integer('plan_batch_no')->nullable();
            $table->string('batch_code', 30)->nullable();
            $table->boolean('batch_completed')->default(true);
            $table->index('batch_code');
        });
    }

    public function down()
    {
        Schema::table('generated_production_orders', function (Blueprint $table) {
            $table->dropIndex(['batch_code']);
        });
        foreach (['plan_line_no', 'plan_batch_no', 'batch_code', 'batch_completed'] as $col) {
            Schema::table('generated_production_orders', function (Blueprint $table) use ($col) {
                $table->dropColumn($col);
            });
        }
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->dropColumn(['released_from', 'batch_uom']);
        });
        Schema::dropIfExists('bc_sync_state');
        Schema::dropIfExists('bc_plan_batches');
    }
}

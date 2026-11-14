<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Released orders posted by BC over HTTP, batch references, weighing with vessel tare, and end of day.
 * - bc_plan_orders: received_at (last HTTP post from BC); close_* for end of day (status 'closing' until BC finishes it).
 * - bc_plan_batches: firm_planned_no, so every WMS batch order carries the BC order (plan_no), the firm planned order
 *   it was released from and the date (due_date).
 * - section_vessels: vessel (crate, tray, trolley ...) tare per WMS section.
 * - plan_batch_weighings: each weighing of a batch (gross - vessels = net); posting the batch sums them per item into
 *   one WMS production order.
 */
class CreatePlanWeighingAndEndOfDay extends Migration
{
    public function up()
    {
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->dateTime('received_at')->nullable();
            $table->dateTime('close_requested_at')->nullable();
            $table->unsignedBigInteger('close_requested_by')->nullable();
            $table->string('close_message', 250)->nullable();
        });
        Schema::table('bc_plan_batches', function (Blueprint $table) {
            $table->string('firm_planned_no', 20)->nullable();
        });
        DB::statement('UPDATE b SET b.firm_planned_no = p.released_from FROM bc_plan_batches b JOIN bc_plan_orders p ON p.plan_no = b.plan_no');

        Schema::create('section_vessels', function (Blueprint $table) {
            $table->id();
            $table->string('section', 30);
            $table->string('name', 50);
            $table->decimal('tare_kg', 10, 3)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->index(['section', 'active']);
        });

        Schema::create('plan_batch_weighings', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 30);
            $table->string('plan_no', 20);
            $table->string('item_no', 20);
            $table->string('description', 100)->nullable();
            $table->string('line_type', 12);                 // output | consumption
            $table->boolean('by_product')->default(false);
            $table->string('uom', 10)->default('KG');
            $table->string('location_code', 10)->nullable();
            $table->string('scale', 50)->nullable();
            $table->boolean('manual')->default(false);
            $table->decimal('gross_kg', 12, 3);
            $table->unsignedBigInteger('vessel_id')->nullable();
            $table->string('vessel_name', 50)->nullable();
            $table->decimal('vessel_tare_kg', 10, 3)->default(0);
            $table->unsignedInteger('vessel_count')->default(0);
            $table->decimal('tare_kg', 12, 3)->default(0);
            $table->decimal('net_kg', 12, 3);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('wms_order_no', 30)->nullable();  // set when the batch is posted
            $table->timestamps();
            $table->index(['batch_code', 'wms_order_no']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_batch_weighings');
        Schema::dropIfExists('section_vessels');
        Schema::table('bc_plan_batches', function (Blueprint $table) {
            $table->dropColumn('firm_planned_no');
        });
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->dropColumn(['received_at', 'close_requested_at', 'close_requested_by', 'close_message']);
        });
    }
}

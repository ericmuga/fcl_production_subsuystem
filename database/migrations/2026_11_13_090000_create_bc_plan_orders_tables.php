<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BC MRP daily plans mirrored in WMS (integration map S4-S5): the day target per item + location, and the plan's
 * components (a negative qty per is a by-product posted as negative consumption).
 */
class CreateBcPlanOrdersTables extends Migration
{
    public function up()
    {
        Schema::create('bc_plan_orders', function (Blueprint $table) {
            $table->id();
            $table->string('plan_no', 20)->unique();
            $table->string('item_no', 20);
            $table->string('description', 100)->nullable();
            $table->string('location_code', 10);
            $table->date('plan_date');
            $table->string('uom', 10)->nullable();
            $table->decimal('planned_qty', 18, 5)->default(0);
            $table->decimal('executed_qty', 18, 5)->default(0);
            $table->decimal('remaining_qty', 18, 5)->default(0);
            // open | closed | withdrawn (no longer published by BC)
            $table->string('status', 12)->default('open');
            $table->string('source', 10)->default('sql');
            $table->dateTime('bc_modified_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->string('closed_by', 50)->nullable();
            $table->string('payload_hash', 64)->nullable();
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();

            $table->index(['plan_date', 'location_code', 'status']);
            $table->index(['item_no', 'location_code', 'plan_date']);
        });

        Schema::create('bc_plan_order_components', function (Blueprint $table) {
            $table->id();
            $table->string('plan_no', 20);
            $table->integer('plan_line_no')->default(0);
            $table->integer('line_no');
            $table->string('item_no', 20);
            $table->string('description', 100)->nullable();
            $table->string('uom', 10)->nullable();
            $table->decimal('qty_per', 18, 5)->default(0);
            $table->decimal('expected_qty', 18, 5)->default(0);
            $table->decimal('remaining_qty', 18, 5)->default(0);
            $table->string('location_code', 10)->nullable();
            $table->string('flushing_method', 20)->nullable();
            $table->boolean('is_by_product')->default(false);
            $table->timestamps();

            $table->index(['plan_no', 'line_no']);
            $table->index('item_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bc_plan_order_components');
        Schema::dropIfExists('bc_plan_orders');
    }
}

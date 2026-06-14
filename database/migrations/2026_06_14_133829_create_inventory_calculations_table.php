<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Inputs
            $table->decimal('d', 12, 4);
            $table->decimal('r_period', 12, 4);
            $table->decimal('lead_time', 12, 4);
            $table->decimal('ordering_cost', 15, 2);
            $table->decimal('item_price', 15, 2);
            $table->decimal('holding_cost', 15, 2);
            $table->decimal('shortage_cost', 15, 2);
            $table->decimal('sigma', 12, 4);
            
            // Intermediates
            $table->decimal('xr', 12, 4);
            $table->decimal('xr_l', 12, 4);
            $table->decimal('z_value', 12, 4);
            $table->decimal('sp', 12, 4);
            
            // Outputs
            $table->decimal('qp', 12, 4);
            $table->decimal('reorder_point', 12, 4);
            $table->decimal('max_inventory', 12, 4);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_calculations');
    }
};

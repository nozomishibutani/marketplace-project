<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('item_id')->unique()->constrained();
            $table->string('postcode', 20);
            $table->string('address');
            $table->string('building')->nullable();
            $table->string('payment_id')->unique();
            $table->tinyInteger('payment_method')
                    ->comment('1=コンビニ支払い, 2=カード支払い');
            $table->string('payment_status');
            $table->timestamp('payment_expires_at')->nullable();
            $table->timestamps();
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

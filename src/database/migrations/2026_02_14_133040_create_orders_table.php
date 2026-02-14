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
            $table->foreignId('item_id')->constrained();
            $table->tinyInteger('payment_method')
                    ->comment('1=コンビニ支払い', '2=カード支払い');
            $table->string('postcode', 20);
            $table->string('address');
            $table->string('building')->nullable();
            $table->tinyInteger('status')->default(1)
                    ->comment('1=支払い待ち, 2=支払い済み, 3=発送済み, 4=キャンセル');
            $table->timestamps();
            $table->softDeletes();
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

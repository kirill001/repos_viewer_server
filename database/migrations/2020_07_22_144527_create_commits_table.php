<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('repo_id')->unsigned();
            $table->string('sha');
            $table->string('url');
            $table->string('message');
            $table->timestamp('date');
            $table->timestamps();

            $table->unique(['repo_id', 'sha']);
            $table->foreign('repo_id')
                ->references('id')
                ->on('repos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commits');
    }
}

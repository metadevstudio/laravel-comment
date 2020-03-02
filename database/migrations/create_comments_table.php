<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommentsTable extends Migration
{
    public function up(): void
    {
        Schema::create($this->commentsTable(), function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('commentable');
            $table->morphs('commented');
            $table->unsignedInteger('reply_to')->nullable();
            $table->longText('comment');
            $table->boolean('approved')->default(true);
            $table->double('rate', 15, 8)->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->timestamps();

            $table->foreign('reply_to')->references('comments')->on('id')->onDelete('CASCADE')->onUpdate('NO ACTION');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->commentsTable());
    }

    private function commentsTable(): string
    {
        $model = config('comment.model');

        return (new $model)->getTable();
    }
}

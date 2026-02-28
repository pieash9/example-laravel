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
        Schema::table('borrows', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('borrow_date');
            $table->string('status')->default('pending')->after('returned');
            $table->string('proof_photo_path')->nullable()->after('status');
            $table->text('requested_note')->nullable()->after('proof_photo_path');
            $table->text('processed_note')->nullable()->after('requested_note');
            $table->foreignId('processed_by')->nullable()->after('processed_note')->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable()->after('processed_by');
            $table->index(['status', 'borrow_date']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropIndex(['status', 'borrow_date']);
            $table->dropIndex(['due_date']);
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn([
                'due_date',
                'status',
                'proof_photo_path',
                'requested_note',
                'processed_note',
                'processed_at',
            ]);
        });
    }
};

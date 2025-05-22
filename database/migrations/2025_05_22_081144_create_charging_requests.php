<?php

declare(strict_types=1);

use App\ChargingRequests\ValueObjects\ChargingRequestStatus;
use App\ChargingSlots\ChargingSlot;
use App\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charging_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('battery_percentage');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', array_column(ChargingRequestStatus::cases(), 'value'));
            $table->foreignIdFor(ChargingSlot::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('charging_started_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('charging_requests', function (Blueprint $table) {
            $table->dropForeignIdFor(User::class);
            $table->dropForeignIdFor(ChargingSlot::class);
        });
        Schema::dropIfExists('charging_requests');
    }
};

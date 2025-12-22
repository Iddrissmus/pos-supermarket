<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a generic activity.
     */
    public static function log(
        string $action,
        string $description,
        $subject = null,
        array $properties = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id ?? null,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log authentication activities.
     */
    public static function logAuth(string $action, string $description, ?int $userId = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log model CRUD operations.
     */
    public static function logModel(
        string $action,
        $model,
        array $oldValues = [],
        array $newValues = []
    ): ActivityLog {
        $modelName = class_basename($model);
        $actionVerb = match($action) {
            'create' => 'created',
            'update' => 'updated',
            'delete' => 'deleted',
            default => $action,
        };

        $description = Auth::user()->name . " {$actionVerb} {$modelName}";
        
        if (method_exists($model, 'name')) {
            $description .= ": {$model->name}";
        } elseif (method_exists($model, 'title')) {
            $description .= ": {$model->title}";
        } elseif (isset($model->id)) {
            $description .= " (ID: {$model->id})";
        }

        $properties = [];
        if (!empty($oldValues)) {
            $properties['old_values'] = $oldValues;
        }
        if (!empty($newValues)) {
            $properties['new_values'] = $newValues;
        }

        return self::log($action, $description, $model, $properties);
    }

    /**
     * Log critical/sensitive actions.
     */
    public static function logCritical(
        string $action,
        string $description,
        array $metadata = []
    ): ActivityLog {
        $properties = [
            'metadata' => array_merge($metadata, [
                'timestamp' => now()->toDateTimeString(),
                'severity' => 'critical',
            ])
        ];

        return self::log($action, $description, null, $properties);
    }

    /**
     * Log failed login attempts.
     */
    public static function logFailedLogin(string $email): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => null,
            'action' => 'failed_login',
            'description' => "Failed login attempt for email: {$email}",
            'properties' => ['email' => $email],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log export actions.
     */
    public static function logExport(string $type, string $description, array $filters = []): ActivityLog
    {
        return self::log(
            'export',
            Auth::user()->name . " exported {$type}: {$description}",
            null,
            ['filters' => $filters, 'export_type' => $type]
        );
    }

    /**
     * Log bulk operations.
     */
    public static function logBulk(string $action, string $description, int $count, array $metadata = []): ActivityLog
    {
        return self::log(
            "bulk_{$action}",
            Auth::user()->name . " {$description} ({$count} items)",
            null,
            array_merge(['count' => $count], $metadata)
        );
    }

    /**
     * Log stock adjustments.
     */
    public static function logStockAdjustment(
        $product,
        string $action,
        int $oldQuantity,
        int $newQuantity,
        string $reason = null
    ): ActivityLog {
        $difference = $newQuantity - $oldQuantity;
        $description = Auth::user()->name . " adjusted stock for {$product->name}: ";
        $description .= $difference > 0 ? "+{$difference}" : $difference;
        $description .= " (from {$oldQuantity} to {$newQuantity})";
        
        if ($reason) {
            $description .= " - Reason: {$reason}";
        }

        return self::log(
            'stock_adjustment',
            $description,
            $product,
            [
                'old_values' => ['quantity' => $oldQuantity],
                'new_values' => ['quantity' => $newQuantity],
                'metadata' => ['reason' => $reason, 'difference' => $difference]
            ]
        );
    }

    /**
     * Log price changes.
     */
    public static function logPriceChange(
        $product,
        float $oldPrice,
        float $newPrice
    ): ActivityLog {
        $difference = $newPrice - $oldPrice;
        $percentChange = $oldPrice > 0 ? round(($difference / $oldPrice) * 100, 2) : 0;
        
        $description = Auth::user()->name . " changed price for {$product->name}: ";
        $description .= "₦" . number_format($oldPrice, 2) . " → ₦" . number_format($newPrice, 2);
        $description .= " ({$percentChange}%)";

        return self::log(
            'price_change',
            $description,
            $product,
            [
                'old_values' => ['price' => $oldPrice],
                'new_values' => ['price' => $newPrice],
                'metadata' => [
                    'difference' => $difference,
                    'percent_change' => $percentChange
                ]
            ]
        );
    }
}

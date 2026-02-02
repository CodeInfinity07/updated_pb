<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_permission', 'permission_id', 'role_id')
            ->withTimestamps();
    }

    public static function getModules(): array
    {
        return self::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->toArray();
    }

    public static function getByModule(): array
    {
        return self::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module')
            ->toArray();
    }

    public static function getModuleLabels(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'budget' => 'Budget Dashboard',
            'analytics' => 'Analytics & Reports',
            'users' => 'User Management',
            'investments' => 'Investments',
            'withdrawals' => 'Withdrawals',
            'deposits' => 'Deposits',
            'kyc' => 'KYC Verification',
            'commission' => 'Commission Settings',
            'leaderboards' => 'Promotions/Leaderboards',
            'announcements' => 'Announcements',
            'support' => 'Support & FAQ',
            'crm' => 'CRM',
            'settings' => 'System Settings',
            'email' => 'Email Settings',
            'push' => 'Push Notifications',
            'roles' => 'Role Management',
            'salary' => 'Monthly Salary Program',
            'logs' => 'System Logs',
        ];
    }

    public function getModuleLabelAttribute(): string
    {
        $labels = self::getModuleLabels();
        return $labels[$this->module] ?? ucfirst($this->module);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all maintenance requests in this category
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'category', 'code');
    }

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get default maintenance categories
     */
    public static function getDefaultCategories()
    {
        return [
            [
                'name' => 'HVAC',
                'code' => 'hvac',
                'description' => 'Heating, Ventilation, and Air Conditioning',
                'color' => '#3b82f6',
                'icon' => 'thermometer',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Plumbing',
                'code' => 'plumbing',
                'description' => 'Water pipes, drains, and bathroom fixtures',
                'color' => '#06b6d4',
                'icon' => 'droplet',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Electrical',
                'code' => 'electrical',
                'description' => 'Lights, outlets, and electrical systems',
                'color' => '#f59e0b',
                'icon' => 'zap',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Carpentry',
                'code' => 'carpentry',
                'description' => 'Doors, furniture, and woodwork',
                'color' => '#8b5cf6',
                'icon' => 'hammer',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Painting',
                'code' => 'painting',
                'description' => 'Wall repairs and painting',
                'color' => '#ec4899',
                'icon' => 'paintbrush',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Appliances',
                'code' => 'appliances',
                'description' => 'Room appliances and equipment',
                'color' => '#10b981',
                'icon' => 'plug',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Structural',
                'code' => 'structural',
                'description' => 'Building structure and walls',
                'color' => '#6366f1',
                'icon' => 'home',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Other',
                'code' => 'other',
                'description' => 'Other maintenance issues',
                'color' => '#64748b',
                'icon' => 'help-circle',
                'is_active' => true,
                'sort_order' => 99,
            ],
        ];
    }
}

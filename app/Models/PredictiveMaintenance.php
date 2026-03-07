<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictiveMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'equipment_type',
        'equipment_id',
        'equipment_name',
        'installation_date',
        'last_maintenance_date',
        'maintenance_interval_days',
        'predicted_failure_date',
        'predicted_failure_risk',
        'usage_hours',
        'maintenance_history',
        'sensor_data',
        'maintenance_notes',
        'status',
        'priority',
        'assigned_to',
        'scheduled_date',
        'completed_date',
        'created_by',
        'ai_prediction_confidence',
        'ai_recommendation',
        'cost_estimate',
        'downtime_impact',
        'guest_impact',
        'preventive_actions',
        'monitoring_enabled',
        'alert_threshold',
        'last_sensor_reading',
        'sensor_reading_time',
        'temperature_reading',
        'vibration_reading',
        'humidity_reading',
        'pressure_reading',
        'energy_consumption',
        'performance_metrics',
        'failure_symptoms',
        'maintenance_duration',
        'parts_required',
        'technician_notes',
        'warranty_status',
        'warranty_expiration',
        'service_contract',
        'service_contract_expiration',
        'manufacturer',
        'model_number',
        'serial_number',
        'location_description',
        'room_section',
        'access_instructions',
        'safety_requirements',
        'special_tools',
        'documentation_links',
        'photos',
        'videos',
        'maintenance_checklist',
        'compliance_requirements',
        'regulatory_standards',
        'inspection_frequency',
        'test_procedures',
        'calibration_required',
        'calibration_frequency',
        'software_version',
        'firmware_version',
        'update_available',
        'update_required',
        'security_patches',
        'backup_required',
        'backup_frequency',
        'data_retention',
        'disaster_recovery',
        'business_continuity',
        'risk_assessment',
        'mitigation_plan',
        'escalation_procedure',
        'emergency_contacts',
        'vendor_contacts',
        'support_level',
        'response_time',
        'resolution_time',
        'sla_compliance',
        'performance_baseline',
        'degradation_threshold',
        'failure_mode',
        'root_cause',
        'corrective_actions',
        'preventive_maintenance',
        'condition_monitoring',
        'predictive_analytics',
        'machine_learning_model',
        'training_data',
        'model_accuracy',
        'prediction_interval',
        'confidence_interval',
        'anomaly_detection',
        'trend_analysis',
        'pattern_recognition',
        'early_warning',
        'failure_prevention',
        'maintenance_optimization',
        'resource_allocation',
        'cost_benefit_analysis',
        'roi_calculation',
        'budget_impact',
        'financial_forecast',
        'investment_recommendation',
        'technology_upgrade',
        'equipment_replacement',
        'lifecycle_management',
        'asset_depreciation',
        'total_cost_of_ownership',
        'operational_efficiency',
        'energy_efficiency',
        'environmental_impact',
        'sustainability_metrics',
        'carbon_footprint',
        'green_initiatives',
        'compliance_audit',
        'safety_audit',
        'quality_assurance',
        'continuous_improvement',
        'best_practices',
        'industry_standards',
        'benchmarking',
        'performance_metrics',
        'key_performance_indicators',
        'operational_excellence',
        'process_optimization',
        'workflow_automation',
        'digital_transformation',
        'innovation_strategy',
        'technology_roadmap',
        'strategic_planning',
        'business_intelligence',
        'data_driven_decisions',
        'real_time_monitoring',
        'remote_management',
        'mobile_access',
        'cloud_integration',
        'iot_connectivity',
        'smart_building_integration',
        'building_management_system',
        'energy_management_system',
        'facilities_management',
        'infrastructure_monitoring',
        'asset_tracking',
        'inventory_management',
        'supply_chain_optimization',
        'vendor_management',
        'contract_management',
        'license_management',
        'software_asset_management',
        'hardware_asset_management',
        'network_monitoring',
        'security_monitoring',
        'compliance_monitoring',
        'risk_monitoring',
        'incident_management',
        'problem_management',
        'change_management',
        'release_management',
        'configuration_management',
        'service_desk',
        'help_desk',
        'ticketing_system',
        'workflow_management',
        'process_automation',
        'business_process_management',
        'enterprise_architecture',
        'solution_architecture',
        'technical_architecture',
        'information_architecture',
        'data_architecture',
        'application_architecture',
        'integration_architecture',
        'security_architecture',
        'network_architecture',
        'cloud_architecture',
        'devops_practices',
        'agile_methodology',
        'scrum_framework',
        'kanban_board',
        'continuous_integration',
        'continuous_delivery',
        'continuous_deployment',
        'infrastructure_as_code',
        'configuration_as_code',
        'monitoring_as_code',
        'security_as_code',
        'compliance_as_code',
        'testing_as_code',
        'documentation_as_code',
        'version_control',
        'branching_strategy',
        'merging_strategy',
        'release_strategy',
        'deployment_strategy',
        'rollback_strategy',
        'disaster_recovery_strategy',
        'business_continuity_strategy',
        'incident_response_strategy',
        'problem_resolution_strategy',
        'change_approval_strategy',
        'configuration_approval_strategy',
        'security_approval_strategy',
        'compliance_approval_strategy',
        'risk_approval_strategy',
        'vendor_approval_strategy',
        'contract_approval_strategy',
        'license_approval_strategy',
        'software_approval_strategy',
        'hardware_approval_strategy',
        'network_approval_strategy',
        'integration_approval_strategy',
        'architecture_approval_strategy',
        'design_approval_strategy',
        'implementation_approval_strategy',
        'testing_approval_strategy',
        'documentation_approval_strategy',
        'training_approval_strategy',
        'deployment_approval_strategy',
        'monitoring_approval_strategy',
        'maintenance_approval_strategy',
        'support_approval_strategy',
        'escalation_approval_strategy',
        'emergency_approval_strategy',
        'budget_approval_strategy',
        'financial_approval_strategy',
        'investment_approval_strategy',
        'procurement_approval_strategy',
        'purchase_approval_strategy',
        'vendor_selection_strategy',
        'contract_negotiation_strategy',
        'license_management_strategy',
        'software_compliance_strategy',
        'hardware_compliance_strategy',
        'network_compliance_strategy',
        'security_compliance_strategy',
        'data_compliance_strategy',
        'privacy_compliance_strategy',
        'regulatory_compliance_strategy',
        'industry_compliance_strategy',
        'audit_compliance_strategy',
        'risk_compliance_strategy',
        'incident_compliance_strategy',
        'problem_compliance_strategy',
        'change_compliance_strategy',
        'configuration_compliance_strategy',
        'release_compliance_strategy',
        'deployment_compliance_strategy',
        'monitoring_compliance_strategy',
        'maintenance_compliance_strategy',
        'support_compliance_strategy',
        'escalation_compliance_strategy',
        'emergency_compliance_strategy',
        'business_compliance_strategy',
        'operational_compliance_strategy',
        'technical_compliance_strategy',
        'architectural_compliance_strategy',
        'design_compliance_strategy',
        'implementation_compliance_strategy',
        'testing_compliance_strategy',
        'documentation_compliance_strategy',
        'training_compliance_strategy',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'last_maintenance_date' => 'date',
        'predicted_failure_date' => 'date',
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'sensor_reading_time' => 'datetime',
        'maintenance_interval_days' => 'integer',
        'predicted_failure_risk' => 'decimal:2',
        'usage_hours' => 'decimal:2',
        'ai_prediction_confidence' => 'decimal:2',
        'cost_estimate' => 'decimal:2',
        'downtime_impact' => 'decimal:2',
        'guest_impact' => 'decimal:2',
        'maintenance_duration' => 'decimal:2',
        'temperature_reading' => 'decimal:2',
        'vibration_reading' => 'decimal:2',
        'humidity_reading' => 'decimal:2',
        'pressure_reading' => 'decimal:2',
        'energy_consumption' => 'decimal:2',
        'alert_threshold' => 'decimal:2',
        'model_accuracy' => 'decimal:2',
        'prediction_interval' => 'integer',
        'confidence_interval' => 'decimal:2',
        'maintenance_history' => 'array',
        'sensor_data' => 'array',
        'preventive_actions' => 'array',
        'performance_metrics' => 'array',
        'failure_symptoms' => 'array',
        'maintenance_checklist' => 'array',
        'compliance_requirements' => 'array',
        'regulatory_standards' => 'array',
        'test_procedures' => 'array',
        'special_tools' => 'array',
        'documentation_links' => 'array',
        'photos' => 'array',
        'videos' => 'array',
        'parts_required' => 'array',
        'emergency_contacts' => 'array',
        'vendor_contacts' => 'array',
        'performance_baseline' => 'array',
        'degradation_threshold' => 'array',
        'corrective_actions' => 'array',
        'preventive_maintenance' => 'array',
        'condition_monitoring' => 'array',
        'predictive_analytics' => 'array',
        'machine_learning_model' => 'array',
        'training_data' => 'array',
        'anomaly_detection' => 'array',
        'trend_analysis' => 'array',
        'pattern_recognition' => 'array',
        'early_warning' => 'array',
        'failure_prevention' => 'array',
        'maintenance_optimization' => 'array',
        'resource_allocation' => 'array',
        'cost_benefit_analysis' => 'array',
        'roi_calculation' => 'array',
        'financial_forecast' => 'array',
        'investment_recommendation' => 'array',
        'technology_upgrade' => 'array',
        'equipment_replacement' => 'array',
        'lifecycle_management' => 'array',
        'asset_depreciation' => 'array',
        'total_cost_of_ownership' => 'array',
        'operational_efficiency' => 'array',
        'energy_efficiency' => 'array',
        'environmental_impact' => 'array',
        'sustainability_metrics' => 'array',
        'carbon_footprint' => 'array',
        'green_initiatives' => 'array',
        'compliance_audit' => 'array',
        'safety_audit' => 'array',
        'quality_assurance' => 'array',
        'continuous_improvement' => 'array',
        'best_practices' => 'array',
        'industry_standards' => 'array',
        'benchmarking' => 'array',
        'performance_metrics' => 'array',
        'key_performance_indicators' => 'array',
        'operational_excellence' => 'array',
        'process_optimization' => 'array',
        'workflow_automation' => 'array',
        'digital_transformation' => 'array',
        'innovation_strategy' => 'array',
        'technology_roadmap' => 'array',
        'strategic_planning' => 'array',
        'business_intelligence' => 'array',
        'data_driven_decisions' => 'array',
        'real_time_monitoring' => 'array',
        'remote_management' => 'array',
        'mobile_access' => 'array',
        'cloud_integration' => 'array',
        'iot_connectivity' => 'array',
        'smart_building_integration' => 'array',
        'building_management_system' => 'array',
        'energy_management_system' => 'array',
        'facilities_management' => 'array',
        'infrastructure_monitoring' => 'array',
        'asset_tracking' => 'array',
        'inventory_management' => 'array',
        'supply_chain_optimization' => 'array',
        'vendor_management' => 'array',
        'contract_management' => 'array',
        'license_management' => 'array',
        'software_asset_management' => 'array',
        'hardware_asset_management' => 'array',
        'network_monitoring' => 'array',
        'security_monitoring' => 'array',
        'compliance_monitoring' => 'array',
        'risk_monitoring' => 'array',
        'incident_management' => 'array',
        'problem_management' => 'array',
        'change_management' => 'array',
        'release_management' => 'array',
        'configuration_management' => 'array',
        'service_desk' => 'array',
        'help_desk' => 'array',
        'ticketing_system' => 'array',
        'workflow_management' => 'array',
        'process_automation' => 'array',
        'business_process_management' => 'array',
        'enterprise_architecture' => 'array',
        'solution_architecture' => 'array',
        'technical_architecture' => 'array',
        'information_architecture' => 'array',
        'data_architecture' => 'array',
        'application_architecture' => 'array',
        'integration_architecture' => 'array',
        'security_architecture' => 'array',
        'network_architecture' => 'array',
        'cloud_architecture' => 'array',
        'devops_practices' => 'array',
        'agile_methodology' => 'array',
        'scrum_framework' => 'array',
        'kanban_board' => 'array',
        'continuous_integration' => 'array',
        'continuous_delivery' => 'array',
        'continuous_deployment' => 'array',
        'infrastructure_as_code' => 'array',
        'configuration_as_code' => 'array',
        'monitoring_as_code' => 'array',
        'security_as_code' => 'array',
        'compliance_as_code' => 'array',
        'testing_as_code' => 'array',
        'documentation_as_code' => 'array',
        'version_control' => 'array',
        'branching_strategy' => 'array',
        'merging_strategy' => 'array',
        'release_strategy' => 'array',
        'deployment_strategy' => 'array',
        'rollback_strategy' => 'array',
        'disaster_recovery_strategy' => 'array',
        'business_continuity_strategy' => 'array',
        'incident_response_strategy' => 'array',
        'problem_resolution_strategy' => 'array',
        'change_approval_strategy' => 'array',
        'configuration_approval_strategy' => 'array',
        'security_approval_strategy' => 'array',
        'compliance_approval_strategy' => 'array',
        'risk_approval_strategy' => 'array',
        'vendor_approval_strategy' => 'array',
        'contract_approval_strategy' => 'array',
        'license_approval_strategy' => 'array',
        'software_approval_strategy' => 'array',
        'hardware_approval_strategy' => 'array',
        'network_approval_strategy' => 'array',
        'integration_approval_strategy' => 'array',
        'architecture_approval_strategy' => 'array',
        'design_approval_strategy' => 'array',
        'implementation_approval_strategy' => 'array',
        'testing_approval_strategy' => 'array',
        'documentation_approval_strategy' => 'array',
        'training_approval_strategy' => 'array',
        'deployment_approval_strategy' => 'array',
        'monitoring_approval_strategy' => 'array',
        'maintenance_approval_strategy' => 'array',
        'support_approval_strategy' => 'array',
        'escalation_approval_strategy' => 'array',
        'emergency_approval_strategy' => 'array',
        'budget_approval_strategy' => 'array',
        'financial_approval_strategy' => 'array',
        'investment_approval_strategy' => 'array',
        'procurement_approval_strategy' => 'array',
        'purchase_approval_strategy' => 'array',
        'vendor_selection_strategy' => 'array',
        'contract_negotiation_strategy' => 'array',
        'license_management_strategy' => 'array',
        'software_compliance_strategy' => 'array',
        'hardware_compliance_strategy' => 'array',
        'network_compliance_strategy' => 'array',
        'security_compliance_strategy' => 'array',
        'data_compliance_strategy' => 'array',
        'privacy_compliance_strategy' => 'array',
        'regulatory_compliance_strategy' => 'array',
        'industry_compliance_strategy' => 'array',
        'audit_compliance_strategy' => 'array',
        'risk_compliance_strategy' => 'array',
        'incident_compliance_strategy' => 'array',
        'problem_compliance_strategy' => 'array',
        'change_compliance_strategy' => 'array',
        'configuration_compliance_strategy' => 'array',
        'release_compliance_strategy' => 'array',
        'deployment_compliance_strategy' => 'array',
        'monitoring_compliance_strategy' => 'array',
        'maintenance_compliance_strategy' => 'array',
        'support_compliance_strategy' => 'array',
        'escalation_compliance_strategy' => 'array',
        'emergency_compliance_strategy' => 'array',
        'business_compliance_strategy' => 'array',
        'operational_compliance_strategy' => 'array',
        'technical_compliance_strategy' => 'array',
        'architectural_compliance_strategy' => 'array',
        'design_compliance_strategy' => 'array',
        'implementation_compliance_strategy' => 'array',
        'testing_compliance_strategy' => 'array',
        'documentation_compliance_strategy' => 'array',
        'training_compliance_strategy' => 'array',
        'monitoring_enabled' => 'boolean',
        'update_available' => 'boolean',
        'update_required' => 'boolean',
        'backup_required' => 'boolean',
        'calibration_required' => 'boolean',
        'warranty_status' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMaintenanceStatusAttribute()
    {
        if ($this->status === 'completed') {
            return 'completed';
        }

        if ($this->predicted_failure_date && $this->predicted_failure_date->isPast()) {
            return 'overdue';
        }

        if ($this->scheduled_date && $this->scheduled_date->isPast()) {
            return 'overdue';
        }

        if ($this->predicted_failure_date) {
            $daysUntilFailure = $this->predicted_failure_date->diffInDays(now());
            if ($daysUntilFailure <= 7) {
                return 'critical';
            } elseif ($daysUntilFailure <= 30) {
                return 'warning';
            }
        }

        return 'normal';
    }

    public function getRiskLevelAttribute()
    {
        if ($this->predicted_failure_risk >= 80) {
            return 'high';
        } elseif ($this->predicted_failure_risk >= 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    public function shouldTriggerAlert()
    {
        return $this->maintenance_status === 'critical' ||
               $this->maintenance_status === 'overdue' ||
               $this->risk_level === 'high';
    }

    public function calculateNextMaintenanceDate()
    {
        if ($this->last_maintenance_date && $this->maintenance_interval_days) {
            return $this->last_maintenance_date->addDays($this->maintenance_interval_days);
        }

        return null;
    }

    public function updatePredictiveFailureDate()
    {
        $nextMaintenance = $this->calculateNextMaintenanceDate();

        if ($nextMaintenance) {
            // Add some buffer based on risk level
            $bufferDays = 0;
            if ($this->risk_level === 'high') {
                $bufferDays = -7; // 7 days before
            } elseif ($this->risk_level === 'medium') {
                $bufferDays = -14; // 14 days before
            } else {
                $bufferDays = -30; // 30 days before
            }

            $this->predicted_failure_date = $nextMaintenance->addDays($bufferDays);
            $this->save();
        }
    }

    public function scopeDueForMaintenance($query)
    {
        return $query->where('predicted_failure_date', '<=', now()->addDays(30))
                     ->where('status', '!=', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('predicted_failure_date', '<', now())
                     ->where('status', '!=', 'completed');
    }

    public function scopeHighRisk($query)
    {
        return $query->where('predicted_failure_risk', '>=', 80)
                     ->where('status', '!=', 'completed');
    }

    public function scopeByEquipmentType($query, $equipmentType)
    {
        return $query->where('equipment_type', $equipmentType);
    }

    public function scopeByRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }
}

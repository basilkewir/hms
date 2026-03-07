<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'guest_id',
        'guest_type_id',
        'title',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'nationality',
        'occupation',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'emergency_contact_address',
        'id_type',
        'id_number',
        'id_issuing_authority',
        'id_issue_date',
        'id_expiry_date',
        'id_document_path',
        'passport_number',
        'passport_issuing_country',
        'passport_issue_date',
        'passport_expiry_date',
        'passport_document_path',
        'visa_number',
        'visa_type',
        'visa_issue_date',
        'visa_expiry_date',
        'visa_document_path',
        'police_verification_status',
        'police_verification_notes',
        'police_verification_date',
        'police_verification_officer',
        'police_case_number',
        'arrival_from',
        'departure_to',
        'purpose_of_visit',
        'expected_duration_days',
        'total_companions',
        'companion_details',
        'vehicle_registration',
        'vehicle_make_model',
        'vehicle_color',
        'preferences',
        'special_requests',
        'medical_conditions',
        'dietary_restrictions',
        'is_blacklisted',
        'blacklist_reason',
        'is_vip',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'id_issue_date' => 'date',
        'id_expiry_date' => 'date',
        'passport_issue_date' => 'date',
        'passport_expiry_date' => 'date',
        'visa_issue_date' => 'date',
        'visa_expiry_date' => 'date',
        'police_verification_date' => 'datetime',
        'companion_details' => 'array',
        'preferences' => 'array',
        'is_blacklisted' => 'boolean',
        'is_vip' => 'boolean',
    ];

    protected $appends = [
        'full_name',
        'age',
        'is_id_expired',
        'is_passport_expired',
        'is_visa_expired',
    ];

    // Relationships
    public function guestType()
    {
        return $this->belongsTo(GuestType::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function folios()
    {
        return $this->hasMany(GuestFolio::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        $name = '';
        if ($this->title) {
            $name .= $this->title . ' ';
        }
        $name .= $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        
        return $name;
    }

    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }
        
        return $this->date_of_birth->diffInYears(now());
    }

    public function getIsIdExpiredAttribute()
    {
        if (!$this->id_expiry_date) {
            return false;
        }
        
        return $this->id_expiry_date->isPast();
    }

    public function getIsPassportExpiredAttribute()
    {
        if (!$this->passport_expiry_date) {
            return false;
        }
        
        return $this->passport_expiry_date->isPast();
    }

    public function getIsVisaExpiredAttribute()
    {
        if (!$this->visa_expiry_date) {
            return false;
        }
        
        return $this->visa_expiry_date->isPast();
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('police_verification_status', 'verified');
    }

    public function scopePendingVerification($query)
    {
        return $query->where('police_verification_status', 'pending');
    }

    public function scopeFlagged($query)
    {
        return $query->where('police_verification_status', 'flagged');
    }

    public function scopeBlacklisted($query)
    {
        return $query->where('is_blacklisted', true);
    }

    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    public function scopeByNationality($query, $nationality)
    {
        return $query->where('nationality', $nationality);
    }

    // Methods
    public function generateGuestId()
    {
        $year = date('Y');
        $lastGuest = self::where('guest_id', 'like', 'G' . $year . '%')
                         ->orderBy('guest_id', 'desc')
                         ->first();
        
        if ($lastGuest) {
            $lastNumber = (int) substr($lastGuest->guest_id, 5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'G' . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function verifyPoliceDetails($officer = null, $notes = null)
    {
        $this->update([
            'police_verification_status' => 'verified',
            'police_verification_date' => now(),
            'police_verification_officer' => $officer,
            'police_verification_notes' => $notes,
        ]);
        
        return $this;
    }

    public function flagForPolice($reason, $caseNumber = null, $officer = null)
    {
        $this->update([
            'police_verification_status' => 'flagged',
            'police_verification_date' => now(),
            'police_verification_officer' => $officer,
            'police_verification_notes' => $reason,
            'police_case_number' => $caseNumber,
        ]);
        
        return $this;
    }

    public function blacklist($reason)
    {
        $this->update([
            'is_blacklisted' => true,
            'blacklist_reason' => $reason,
        ]);
        
        return $this;
    }

    public function removeFromBlacklist()
    {
        $this->update([
            'is_blacklisted' => false,
            'blacklist_reason' => null,
        ]);
        
        return $this;
    }

    public function makeVip()
    {
        $this->update(['is_vip' => true]);
        return $this;
    }

    public function removeVipStatus()
    {
        $this->update(['is_vip' => false]);
        return $this;
    }

    public function hasValidDocuments()
    {
        // Check if primary ID is valid
        if ($this->is_id_expired) {
            return false;
        }
        
        // Check passport if foreign national
        if ($this->nationality !== 'USA' && $this->passport_number) {
            if ($this->is_passport_expired) {
                return false;
            }
        }
        
        // Check visa if required
        if ($this->visa_number && $this->is_visa_expired) {
            return false;
        }
        
        return true;
    }

    public function getDocumentExpiryWarnings()
    {
        $warnings = [];
        $warningDays = 30; // Warn 30 days before expiry
        
        if ($this->id_expiry_date && $this->id_expiry_date->diffInDays(now()) <= $warningDays) {
            $warnings[] = 'ID expires on ' . $this->id_expiry_date->format('M d, Y');
        }
        
        if ($this->passport_expiry_date && $this->passport_expiry_date->diffInDays(now()) <= $warningDays) {
            $warnings[] = 'Passport expires on ' . $this->passport_expiry_date->format('M d, Y');
        }
        
        if ($this->visa_expiry_date && $this->visa_expiry_date->diffInDays(now()) <= $warningDays) {
            $warnings[] = 'Visa expires on ' . $this->visa_expiry_date->format('M d, Y');
        }
        
        return $warnings;
    }

    public function getCurrentReservation()
    {
        return $this->reservations()
                   ->where('status', 'checked_in')
                   ->where('check_in_date', '<=', now())
                   ->where('check_out_date', '>=', now())
                   ->first();
    }

    public function getUpcomingReservation()
    {
        return $this->reservations()
                   ->where('status', 'confirmed')
                   ->where('check_in_date', '>', now())
                   ->orderBy('check_in_date')
                   ->first();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($guest) {
            if (!$guest->guest_id) {
                $guest->guest_id = $guest->generateGuestId();
            }
        });
    }
}

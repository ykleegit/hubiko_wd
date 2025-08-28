<?php

return [
    'name' => 'Booking',
    'version' => '1.0.0',
    'description' => 'Universal booking management system for service-based businesses including salons, beauty, spa, medical, fitness, and more',
    
    // Business types supported
    'business_types' => [
        'salon' => 'Hair Salon',
        'beauty' => 'Beauty Salon',
        'spa' => 'Spa & Wellness',
        'medical' => 'Medical Clinic',
        'dental' => 'Dental Clinic',
        'fitness' => 'Fitness Center',
        'automotive' => 'Auto Service',
        'photography' => 'Photography Studio',
        'consulting' => 'Consulting Services',
        'education' => 'Education & Training',
        'hotel' => 'Hotel & Accommodation'
    ],
    
    // Service categories by business type
    'service_categories' => [
        'salon' => [
            'Hair Cut & Styling',
            'Hair Coloring',
            'Hair Treatment',
            'Nail Services',
            'Makeup',
            'Eyebrow & Lashes'
        ],
        'beauty' => [
            'Facial Treatment',
            'Skin Care',
            'Body Treatment',
            'Massage',
            'Waxing',
            'Permanent Makeup'
        ],
        'spa' => [
            'Relaxation Massage',
            'Therapeutic Massage',
            'Body Scrub',
            'Aromatherapy',
            'Hot Stone Therapy',
            'Couples Treatment'
        ],
        'medical' => [
            'General Consultation',
            'Specialist Consultation',
            'Diagnostic Tests',
            'Procedures',
            'Follow-up',
            'Emergency'
        ],
        'fitness' => [
            'Personal Training',
            'Group Classes',
            'Nutrition Consultation',
            'Fitness Assessment',
            'Rehabilitation',
            'Sports Therapy'
        ]
    ],
    
    // Resource types by business type
    'resource_types' => [
        'salon' => [
            'Hair Washing Station',
            'Styling Chair',
            'Hair Dryer',
            'Manicure Table',
            'Pedicure Chair',
            'Makeup Station'
        ],
        'beauty' => [
            'Treatment Room',
            'Facial Bed',
            'Massage Table',
            'Waxing Room',
            'Steam Room',
            'Equipment Room'
        ],
        'spa' => [
            'Private Suite',
            'Couples Room',
            'Sauna',
            'Jacuzzi',
            'Relaxation Lounge',
            'Therapy Pool'
        ],
        'medical' => [
            'Consultation Room',
            'Examination Room',
            'Procedure Room',
            'X-Ray Room',
            'Laboratory',
            'Operating Theater'
        ],
        'fitness' => [
            'Personal Training Room',
            'Group Exercise Studio',
            'Yoga Studio',
            'Cardio Area',
            'Weight Room',
            'Recovery Room'
        ]
    ],
    
    // Default room types (for hotel/accommodation)
    'room_types' => [
        'single' => 'Single Room',
        'double' => 'Double Room',
        'suite' => 'Suite',
        'deluxe' => 'Deluxe Room',
        'family' => 'Family Room',
        'presidential' => 'Presidential Suite'
    ],
    
    // Appointment settings
    'appointment' => [
        'default_duration' => 60, // minutes
        'buffer_time' => 15, // minutes between appointments
        'advance_booking_days' => 30,
        'min_advance_booking_hours' => 2,
        'cancellation_hours' => 24,
        'reminder_hours' => [24, 2], // send reminders 24h and 2h before
        'time_slots' => [
            'start' => '09:00',
            'end' => '18:00',
            'interval' => 30 // minutes
        ]
    ],
    
    // Booking settings (for hotel/accommodation)
    'booking' => [
        'advance_booking_days' => 365,
        'check_in_time' => '14:00',
        'check_out_time' => '12:00',
        'cancellation_hours' => 24,
    ],
    
    // Payment settings
    'payment' => [
        'deposit_percentage' => 20,
        'currency' => 'USD',
        'tax_percentage' => 10,
        'payment_methods' => ['cash', 'card', 'online', 'bank_transfer']
    ],
    
    // Staff settings
    'staff' => [
        'default_working_hours' => [
            'Monday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
            'Tuesday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
            'Wednesday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
            'Thursday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
            'Friday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
            'Saturday' => ['is_working' => false, 'start' => '10:00', 'end' => '16:00'],
            'Sunday' => ['is_working' => false, 'start' => '10:00', 'end' => '16:00']
        ]
    ]
];

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        body { background: #f8f9fa; min-height: 100vh; }
        
        .leaderboard-header {
            background: linear-gradient(135deg, #fff, #f8f9fa);
            border: 1px solid #e9ecef;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        
        .podium-section {
            background: linear-gradient(180deg, #fff9e6 0%, #fff 100%);
            padding: 30px 15px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            border: 1px solid #f0e6d2;
        }
        
        .podium-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .champion-card {
            position: relative;
            text-align: center;
            padding: 20px 15px;
            border-radius: 20px;
            transition: transform 0.3s ease;
            background: #fff;
        }
        
        .champion-card:hover {
            transform: translateY(-5px);
        }
        
        .champion-card.gold {
            background: linear-gradient(135deg, #fffef5, #fff9e6);
            border: 2px solid #ffd700;
            box-shadow: 0 10px 40px rgba(255,215,0,0.2);
        }
        
        .champion-card.silver {
            background: linear-gradient(135deg, #fafafa, #f0f0f0);
            border: 2px solid #c0c0c0;
            box-shadow: 0 8px 30px rgba(192,192,192,0.2);
        }
        
        .champion-card.bronze {
            background: linear-gradient(135deg, #fdf8f3, #f9efe6);
            border: 2px solid #cd7f32;
            box-shadow: 0 8px 30px rgba(205,127,50,0.15);
        }
        
        .avatar-frame {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
        }
        
        .avatar-frame.gold-frame::before {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffd700, #ffb700, #ffd700);
            z-index: 0;
            animation: rotate 3s linear infinite;
        }
        
        .avatar-frame.silver-frame::before {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c0c0c0, #e8e8e8, #c0c0c0);
            z-index: 0;
        }
        
        .avatar-frame.bronze-frame::before {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            background: linear-gradient(135deg, #cd7f32, #daa06d, #cd7f32);
            z-index: 0;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .avatar-inner {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #e9ecef, #dee2e6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            border: 3px solid #fff;
        }
        
        .crown-icon {
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }
        
        .crown-icon.gold { color: #ffd700; font-size: 32px; filter: drop-shadow(0 2px 4px rgba(255,215,0,0.5)); }
        .crown-icon.silver { color: #a8a8a8; font-size: 28px; filter: drop-shadow(0 2px 4px rgba(192,192,192,0.5)); }
        .crown-icon.bronze { color: #cd7f32; font-size: 26px; filter: drop-shadow(0 2px 4px rgba(205,127,50,0.5)); }
        
        .medal-badge {
            position: absolute;
            bottom: -8px;
            right: -8px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .medal-badge.gold { background: linear-gradient(135deg, #ffd700, #ffb700); }
        .medal-badge.silver { background: linear-gradient(135deg, #e8e8e8, #c0c0c0); }
        .medal-badge.bronze { background: linear-gradient(135deg, #daa06d, #cd7f32); }
        
        .medal-badge iconify-icon { font-size: 18px; }
        .medal-badge.gold iconify-icon { color: #8b6914; }
        .medal-badge.silver iconify-icon { color: #5a5a5a; }
        .medal-badge.bronze iconify-icon { color: #fff; }
        
        .champion-name { color: #212529; font-weight: 600; font-size: 1rem; margin-bottom: 5px; }
        .champion-score { font-size: 1.5rem; font-weight: 700; }
        .champion-score.gold { color: #b8860b; }
        .champion-score.silver { color: #6c757d; }
        .champion-score.bronze { color: #cd7f32; }
        .champion-prize { color: #198754; font-weight: 600; font-size: 1.1rem; }
        .champion-label { color: #6c757d; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .ranking-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .ranking-card:hover {
            background: #f8f9fa;
            border-color: #dee2e6;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .ranking-card .rank-num {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .ranking-card .rank-num.top-5 {
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            color: #4f46e5;
            border: 1px solid #a5b4fc;
        }
        
        .ranking-card .rank-num.normal {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #e9ecef;
        }
        
        .ranking-card .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .ranking-card .user-name {
            color: #212529;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .ranking-card .user-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
        }
        
        .ranking-card .stats {
            text-align: right;
            flex-shrink: 0;
        }
        
        .ranking-card .referral-count {
            color: #0d6efd;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .ranking-card .prize-amount {
            color: #198754;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .stats-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .stats-card .stats-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-card .stats-label {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .your-position-card {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border: 2px solid #fbbf24;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(251,191,36,0.15);
        }
        
        .prize-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .prize-list-item:last-child {
            border-bottom: none;
        }
        
        .section-title {
            color: #212529;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .text-gold { color: #b8860b; }
        .text-silver { color: #6c757d; }
        .text-bronze { color: #cd7f32; }
        
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body>
<div class="container-fluid py-4">

    <div class="alert alert-info mb-4">
        <strong>Demo Mode:</strong> This is a preview with sample data. The actual leaderboard uses real user data.
    </div>

    @php
        $demoLeaderboard = (object)[
            'id' => 1,
            'title' => 'December Referral Challenge',
            'description' => 'Refer the most users and win amazing prizes! Top referrers will receive cash bonuses.',
            'type' => 'competitive',
            'status' => 'active',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(12),
        ];
        
        $demoStats = [
            'total_prize_amount' => 1500,
            'total_participants' => 47,
            'total_winners' => 10,
            'days_remaining' => 12,
            'progress' => 45,
        ];

        $demoPositions = [
            (object)['position' => 1, 'user' => (object)['first_name' => 'John', 'last_name' => 'Doe'], 'referral_count' => 47, 'prize_amount' => 500],
            (object)['position' => 2, 'user' => (object)['first_name' => 'Sarah', 'last_name' => 'Miller'], 'referral_count' => 42, 'prize_amount' => 300],
            (object)['position' => 3, 'user' => (object)['first_name' => 'Mike', 'last_name' => 'Kumar'], 'referral_count' => 38, 'prize_amount' => 200],
            (object)['position' => 4, 'user' => (object)['first_name' => 'Emma', 'last_name' => 'Wilson'], 'referral_count' => 31, 'prize_amount' => 100],
            (object)['position' => 5, 'user' => (object)['first_name' => 'Robert', 'last_name' => 'Johnson'], 'referral_count' => 28, 'prize_amount' => 100],
            (object)['position' => 6, 'user' => (object)['first_name' => 'Anna', 'last_name' => 'Lee'], 'referral_count' => 25, 'prize_amount' => 50],
            (object)['position' => 7, 'user' => (object)['first_name' => 'Tom', 'last_name' => 'Chen'], 'referral_count' => 22, 'prize_amount' => 50],
            (object)['position' => 8, 'user' => (object)['first_name' => 'Lisa', 'last_name' => 'Park'], 'referral_count' => 19, 'prize_amount' => 50],
            (object)['position' => 9, 'user' => (object)['first_name' => 'David', 'last_name' => 'Garcia'], 'referral_count' => 17, 'prize_amount' => 50],
            (object)['position' => 10, 'user' => (object)['first_name' => 'Nina', 'last_name' => 'Brown'], 'referral_count' => 15, 'prize_amount' => 50],
        ];

        $demoUserPosition = (object)[
            'position' => 24,
            'referral_count' => 8,
        ];
    @endphp

    <div class="leaderboard-header p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-3">
                    <iconify-icon icon="akar-icons:trophy" class="text-warning me-3" style="font-size: 48px;"></iconify-icon>
                    <div>
                        <h2 class="text-dark mb-1">{{ $demoLeaderboard->title }}</h2>
                        <p class="text-muted mb-0">{{ $demoLeaderboard->description }}</p>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-6 col-md-3">
                        <div class="stats-card">
                            <div class="stats-value text-warning">${{ number_format($demoStats['total_prize_amount']) }}</div>
                            <div class="stats-label">Prize Pool</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card">
                            <div class="stats-value text-info">{{ $demoStats['total_participants'] }}</div>
                            <div class="stats-label">Participants</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card">
                            <div class="stats-value text-success">{{ $demoStats['total_winners'] }}</div>
                            <div class="stats-label">Winners</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card">
                            <div class="stats-value text-danger">{{ $demoStats['days_remaining'] }}</div>
                            <div class="stats-label">Days Left</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="text-lg-end">
                    <span class="badge bg-success fs-6 mb-2">
                        <iconify-icon icon="iconamoon:lightning-duotone" class="me-1"></iconify-icon>
                        Live Competition
                    </span>
                    <div class="progress mt-3" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: {{ $demoStats['progress'] }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">{{ $demoStats['progress'] }}% complete</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="podium-section mb-4">
                <h6 class="text-center text-muted mb-4 text-uppercase" style="letter-spacing: 2px;">Champions</h6>
                
                <div class="row justify-content-center align-items-end g-3">
                    <div class="col-4 col-md-3 order-2 order-md-1">
                        <div class="champion-card silver">
                            <div class="crown-icon silver">
                                <iconify-icon icon="mdi:crown"></iconify-icon>
                            </div>
                            <div class="avatar-frame silver-frame">
                                <div class="avatar-inner">
                                    <iconify-icon icon="iconamoon:profile-duotone" class="text-secondary" style="font-size: 32px;"></iconify-icon>
                                </div>
                                <div class="medal-badge silver">
                                    <iconify-icon icon="mdi:medal"></iconify-icon>
                                </div>
                            </div>
                            <div class="champion-name">Sarah M.</div>
                            <div class="champion-label">Referrals</div>
                            <div class="champion-score silver">42</div>
                            <div class="champion-prize">$300</div>
                        </div>
                    </div>
                    
                    <div class="col-4 col-md-3 order-1 order-md-2">
                        <div class="champion-card gold" style="transform: scale(1.1);">
                            <div class="crown-icon gold">
                                <iconify-icon icon="mdi:crown"></iconify-icon>
                            </div>
                            <div class="avatar-frame gold-frame">
                                <div class="avatar-inner">
                                    <iconify-icon icon="iconamoon:profile-duotone" class="text-warning" style="font-size: 36px;"></iconify-icon>
                                </div>
                                <div class="medal-badge gold">
                                    <iconify-icon icon="mdi:medal"></iconify-icon>
                                </div>
                            </div>
                            <div class="champion-name">John D.</div>
                            <div class="champion-label">Referrals</div>
                            <div class="champion-score gold">47</div>
                            <div class="champion-prize">$500</div>
                        </div>
                    </div>
                    
                    <div class="col-4 col-md-3 order-3">
                        <div class="champion-card bronze">
                            <div class="crown-icon bronze">
                                <iconify-icon icon="mdi:crown"></iconify-icon>
                            </div>
                            <div class="avatar-frame bronze-frame">
                                <div class="avatar-inner">
                                    <iconify-icon icon="iconamoon:profile-duotone" style="font-size: 30px; color: #cd7f32;"></iconify-icon>
                                </div>
                                <div class="medal-badge bronze">
                                    <iconify-icon icon="mdi:medal"></iconify-icon>
                                </div>
                            </div>
                            <div class="champion-name">Mike K.</div>
                            <div class="champion-label">Referrals</div>
                            <div class="champion-score bronze">38</div>
                            <div class="champion-prize">$200</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <iconify-icon icon="iconamoon:ranking-duotone" class="text-primary"></iconify-icon>
                All Rankings
            </div>
            
            @foreach($demoPositions as $position)
                @if($position->position > 3)
                <div class="ranking-card">
                    <div class="rank-num {{ $position->position <= 5 ? 'top-5' : 'normal' }}">
                        #{{ $position->position }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ $position->user->first_name }} {{ $position->user->last_name }}</div>
                        @if($position->position <= 5)
                            <span class="user-badge bg-primary text-white">Top 5</span>
                        @else
                            <span class="user-badge bg-secondary text-white">Winner</span>
                        @endif
                    </div>
                    <div class="stats">
                        <div class="referral-count">{{ $position->referral_count }}</div>
                        <div class="prize-amount">${{ number_format($position->prize_amount) }}</div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <div class="col-lg-4">
            <div class="your-position-card mb-4">
                <div class="text-center">
                    <iconify-icon icon="iconamoon:star-duotone" class="text-warning mb-2" style="font-size: 40px;"></iconify-icon>
                    <h5 class="text-dark mb-3">Your Position</h5>
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: rgba(251,191,36,0.2); border: 2px solid #fbbf24;">
                        <span class="text-warning fw-bold" style="font-size: 2rem;">#{{ $demoUserPosition->position }}</span>
                    </div>
                    <h4 class="text-dark mb-1">{{ $demoUserPosition->referral_count }} Referrals</h4>
                    <p class="text-muted mb-3">Keep going!</p>
                    
                    <div class="p-3 rounded" style="background: rgba(255,255,255,0.7);">
                        <div class="d-flex justify-content-between text-muted mb-2">
                            <span>Progress to Top 10</span>
                            <span class="text-dark fw-bold">{{ $demoUserPosition->referral_count }}/15</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ ($demoUserPosition->referral_count / 15) * 100 }}%"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">7 more to reach Top 10</small>
                    </div>
                </div>
            </div>

            <div class="stats-card mb-4">
                <div class="section-title justify-content-center">
                    <iconify-icon icon="iconamoon:gift-duotone" class="text-success"></iconify-icon>
                    <span class="text-dark">Prize Structure</span>
                </div>
                
                <div class="prize-list-item">
                    <div class="d-flex align-items-center">
                        <iconify-icon icon="mdi:medal" class="me-2" style="font-size: 24px; color: #ffd700;"></iconify-icon>
                        <span class="text-dark">1st Place</span>
                    </div>
                    <span class="text-success fw-bold">$500</span>
                </div>
                <div class="prize-list-item">
                    <div class="d-flex align-items-center">
                        <iconify-icon icon="mdi:medal" class="me-2" style="font-size: 22px; color: #c0c0c0;"></iconify-icon>
                        <span class="text-dark">2nd Place</span>
                    </div>
                    <span class="text-success fw-bold">$300</span>
                </div>
                <div class="prize-list-item">
                    <div class="d-flex align-items-center">
                        <iconify-icon icon="mdi:medal" class="me-2" style="font-size: 20px; color: #cd7f32;"></iconify-icon>
                        <span class="text-dark">3rd Place</span>
                    </div>
                    <span class="text-success fw-bold">$200</span>
                </div>
                <div class="prize-list-item">
                    <div class="d-flex align-items-center">
                        <iconify-icon icon="iconamoon:star-duotone" class="text-primary me-2" style="font-size: 20px;"></iconify-icon>
                        <span class="text-dark">4th - 5th</span>
                    </div>
                    <span class="text-success fw-bold">$100</span>
                </div>
                <div class="prize-list-item">
                    <div class="d-flex align-items-center">
                        <iconify-icon icon="iconamoon:check-circle-duotone" class="text-info me-2" style="font-size: 20px;"></iconify-icon>
                        <span class="text-dark">6th - 10th</span>
                    </div>
                    <span class="text-success fw-bold">$50</span>
                </div>
            </div>

            <div class="stats-card">
                <div class="section-title justify-content-center">
                    <iconify-icon icon="iconamoon:information-circle-duotone" class="text-info"></iconify-icon>
                    <span class="text-dark">Rules</span>
                </div>
                <ul class="list-unstyled text-start mb-0">
                    <li class="text-muted mb-2 d-flex align-items-start">
                        <iconify-icon icon="iconamoon:check-circle-duotone" class="text-success me-2 mt-1"></iconify-icon>
                        Referred users must invest
                    </li>
                    <li class="text-muted mb-2 d-flex align-items-start">
                        <iconify-icon icon="iconamoon:check-circle-duotone" class="text-success me-2 mt-1"></iconify-icon>
                        Rankings update in real-time
                    </li>
                    <li class="text-muted d-flex align-items-start">
                        <iconify-icon icon="iconamoon:check-circle-duotone" class="text-success me-2 mt-1"></iconify-icon>
                        Prizes awarded after competition ends
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

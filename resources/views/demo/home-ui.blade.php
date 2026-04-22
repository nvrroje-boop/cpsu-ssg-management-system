<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SSG Management System — CPSU Hinoba-an Campus</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet" />
  <style>
    /* -----------------------------------------------
       DESIGN TOKENS — CPSU SSG Color System
       Derived from campus (greens/yellows) + logo (blue, gold)
    ----------------------------------------------- */
    :root {
      /* Primary — deep campus green */
      --primary:          #1B5E20;
      --primary-mid:      #2E7D32;
      --primary-light:    #388E3C;
      --primary-pale:     #E8F5E9;
      --primary-glow:     rgba(27, 94, 32, 0.20);

      /* Secondary — logo blue */
      --secondary:        #0D47A1;
      --secondary-mid:    #1565C0;
      --secondary-pale:   #E3F2FD;

      /* Accent — torch gold */
      --accent:           #F9A825;
      --accent-deep:      #E65100;
      --accent-pale:      #FFF8E1;
      --accent-glow:      rgba(249, 168, 37, 0.25);

      /* Neutrals */
      --ink:              #1A2D1A;
      --ink-mid:          #3D5A3D;
      --muted:            #607D63;
      --subtle:           #9DB59E;
      --surface:          #FFFFFF;
      --surface-dim:      #F5FAF5;
      --surface-alt:      #EBF3EC;
      --border:           rgba(27, 94, 32, 0.12);
      --border-soft:      rgba(0,0,0,0.07);

      /* Category palette */
      --cat-announce:     #0D47A1;
      --cat-event:        #1B5E20;
      --cat-program:      #E65100;
      --cat-announce-bg:  #E3F2FD;
      --cat-event-bg:     #E8F5E9;
      --cat-program-bg:   #FFF3E0;

      /* Shadows */
      --shadow-xs:  0 1px 3px rgba(27,94,32,0.08);
      --shadow-sm:  0 4px 12px rgba(27,94,32,0.10);
      --shadow-md:  0 8px 28px rgba(27,94,32,0.13);
      --shadow-lg:  0 16px 48px rgba(27,94,32,0.16);
      --shadow-card:0 2px 8px rgba(27,94,32,0.07), 0 8px 24px rgba(27,94,32,0.06);

      /* Radii */
      --r-sm: 8px; --r-md: 14px; --r-lg: 20px;
      --r-xl: 28px; --r-pill: 999px;

      /* Motion */
      --ease: cubic-bezier(0.22, 0.61, 0.36, 1);
      --dur:  0.22s;
      --dur-s:0.38s;
    }

    /* --- RESET --- */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem; line-height: 1.6;
      color: var(--ink);
      background: var(--surface-dim);
      overflow-x: hidden;
    }
    img { display: block; max-width: 100%; }
    a { color: inherit; text-decoration: none; }
    :focus-visible { outline: 2.5px solid var(--primary); outline-offset: 3px; border-radius: var(--r-sm); }

    .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 28px; }

    .topbar {
      position: sticky; top: 0; z-index: 200;
      background: rgba(255,255,255,0.94);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border-soft);
      box-shadow: 0 1px 10px rgba(27,94,32,0.08);
      transition: background var(--dur) var(--ease), box-shadow var(--dur) var(--ease);
    }

    .topbar .container {
      display: flex; align-items: center; justify-content: space-between;
      gap: 20px; height: 68px;
    }

    .brand-group { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
    .brand-logo-wrap { width: 46px; height: 46px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(27, 94, 32, 0.14); overflow: hidden; box-shadow: 0 4px 12px rgba(27, 94, 32, 0.12); flex-shrink: 0; }
    .brand-logo-wrap img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    .brand-text { display: flex; flex-direction: column; line-height: 1.2; }
    .brand-title { font-family: 'DM Serif Display', serif; font-size: 0.95rem; font-weight: 400; color: var(--primary); letter-spacing: 0.01em; }
    .brand-sub { font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.09em; color: var(--muted); }

    .nav-links { display: flex; align-items: center; gap: 2px; list-style: none; }
    .nav-links a { font-size: 0.875rem; font-weight: 500; color: var(--muted); padding: 7px 16px; border-radius: var(--r-pill); transition: color var(--dur) var(--ease), background var(--dur) var(--ease); }
    .nav-links a:hover { color: var(--primary); background: var(--primary-pale); }
    .nav-links a.active { color: var(--primary); background: var(--primary-pale); font-weight: 600; }

    .btn-portal { display: inline-flex; align-items: center; gap: 7px; padding: 10px 22px; border-radius: var(--r-pill); background: var(--primary); color: #fff; font-family: 'DM Sans', sans-serif; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 3px 12px var(--primary-glow); transition: background var(--dur) var(--ease), box-shadow var(--dur) var(--ease), transform var(--dur) var(--ease); flex-shrink: 0; }
    .btn-portal:hover { background: var(--primary-mid); color: #fff; box-shadow: 0 5px 20px var(--primary-glow); transform: translateY(-1px); }
    .btn-portal svg { width: 15px; height: 15px; }

    .nav-toggle { display: none; flex-direction: column; justify-content: center; gap: 5px; width: 36px; height: 36px; background: none; border: none; cursor: pointer; padding: 4px; }
    .nav-toggle span { display: block; height: 2px; width: 100%; background: var(--ink); border-radius: 2px; transition: transform var(--dur) var(--ease), opacity var(--dur) var(--ease); }

    .hero { position: relative; min-height: 92svh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 100px 28px 120px; overflow: hidden; }
    .hero-bg { position: absolute; inset: 0; z-index: 0; background: url('{{ asset('campus-building.jpeg') }}') center 34% / cover no-repeat; transform: scale(1.03); transition: transform 8s ease; filter: brightness(0.56) saturate(1.02) contrast(1.04); }
    .hero-overlay { position: absolute; inset: 0; z-index: 1; background: linear-gradient(160deg, rgba(27, 94, 32, 0.54) 0%, rgba(13, 71, 161, 0.26) 48%, rgba(27, 94, 32, 0.58) 100%); }
    .hero-deco { position: absolute; z-index: 2; border-radius: 50%; pointer-events: none; }
    .hero-deco--a { width: 700px; height: 700px; top: -200px; right: -180px; background: radial-gradient(circle, rgba(249,168,37,0.09) 0%, transparent 70%); }
    .hero-deco--b { width: 500px; height: 500px; bottom: -120px; left: -100px; background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%); }
    .hero-emblem { position: relative; z-index: 3; width: 126px; height: 126px; padding: 6px; border-radius: 50%; background: rgba(255,255,255,0.10); border: 2px solid rgba(255,255,255,0.34); display: flex; align-items: center; justify-content: center; margin: 0 auto 28px; overflow: hidden; backdrop-filter: blur(10px); box-shadow: 0 14px 40px rgba(0,0,0,0.28); animation: float-emblem 4s ease-in-out infinite; }
    .hero-emblem img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    @keyframes float-emblem { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    .eyebrow { position: relative; z-index: 3; display: inline-flex; align-items: center; gap: 7px; margin-bottom: 20px; padding: 6px 16px; border-radius: var(--r-pill); background: rgba(249,168,37,0.18); border: 1.5px solid rgba(249,168,37,0.45); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #FFD54F; }
    .eyebrow .pulse { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); flex-shrink: 0; animation: pulse-dot 2.2s ease-in-out infinite; }
    @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.75)} }
    .hero-title { position: relative; z-index: 3; font-family: 'DM Serif Display', serif; font-size: clamp(2.4rem, 6vw, 4.4rem); font-weight: 400; line-height: 1.1; color: #FFFFFF; letter-spacing: -0.01em; max-width: 18ch; margin: 0 auto 18px; }
    .hero-title em { font-style: italic; color: #FFD54F; }
    .hero-lead { position: relative; z-index: 3; max-width: 54ch; margin: 0 auto 40px; font-size: 1.05rem; font-weight: 400; line-height: 1.85; color: rgba(255,255,255,0.72); }
    .hero-cta { position: relative; z-index: 3; display: flex; flex-wrap: wrap; justify-content: center; gap: 14px; }
    .btn-primary { display: inline-flex; align-items: center; gap: 8px; padding: 15px 32px; border-radius: var(--r-md); background: var(--accent); color: var(--ink); font-family: 'DM Sans', sans-serif; font-size: 0.95rem; font-weight: 700; border: none; cursor: pointer; box-shadow: 0 4px 20px var(--accent-glow); transition: background var(--dur) var(--ease), transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease); }
    .btn-primary:hover { background: #FFB300; color: var(--ink); transform: translateY(-2px); box-shadow: 0 8px 28px var(--accent-glow); }
    .btn-ghost { display: inline-flex; align-items: center; gap: 8px; padding: 14px 30px; border-radius: var(--r-md); background: transparent; color: rgba(255,255,255,0.88); font-family: 'DM Sans', sans-serif; font-size: 0.95rem; font-weight: 600; border: 1.5px solid rgba(255,255,255,0.35); cursor: pointer; transition: background var(--dur) var(--ease), border-color var(--dur) var(--ease), transform var(--dur) var(--ease); }
    .btn-ghost:hover { background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.65); color: #fff; transform: translateY(-2px); }
    .scroll-hint { position: absolute; bottom: 32px; left: 50%; transform: translateX(-50%); z-index: 3; display: flex; flex-direction: column; align-items: center; gap: 6px; font-size: 0.67rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.38); animation: bounce-hint 2.8s ease-in-out infinite; }
    @keyframes bounce-hint { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(8px)} }
    .scroll-hint svg { width: 20px; height: 20px; }
    .stats-bar { background: var(--surface); border-bottom: 1px solid var(--border-soft); box-shadow: var(--shadow-xs); }
    .stats-bar .container { display: flex; align-items: stretch; }
    .stat-item { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 22px 16px; border-right: 1px solid var(--border-soft); gap: 3px; }
    .stat-item:last-child { border-right: none; }
    .stat-num { font-family: 'DM Serif Display', serif; font-size: 1.8rem; color: var(--primary); line-height: 1; }
    .stat-lbl { font-size: 0.73rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.07em; }
    .feed { background: var(--surface-dim); padding-bottom: 80px; }
    .section { padding: 64px 0 0; }
    .section-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 30px; }
    .section-label { display: flex; align-items: center; gap: 10px; }
    .section-dot { width: 12px; height: 12px; border-radius: 50%; background: var(--primary); flex-shrink: 0; }
    .section--events .section-dot { background: var(--secondary); }
    .section--programs .section-dot { background: var(--accent-deep); }
    .section-title { font-family: 'DM Serif Display', serif; font-size: 1.55rem; font-weight: 400; color: var(--ink); letter-spacing: -0.01em; }
    .view-all { font-size: 0.83rem; font-weight: 600; color: var(--primary); display: inline-flex; align-items: center; gap: 4px; transition: gap var(--dur) var(--ease), color var(--dur) var(--ease); white-space: nowrap; }
    .view-all:hover { color: var(--primary-mid); gap: 8px; }
    .card-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
    .card { background: var(--surface); border: 1px solid var(--border-soft); border-radius: var(--r-lg); box-shadow: var(--shadow-card); overflow: hidden; display: flex; flex-direction: column; cursor: pointer; transition: transform var(--dur-s) var(--ease), box-shadow var(--dur-s) var(--ease); }
    .card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .card-strip { height: 4px; flex-shrink: 0; }
    .card--announce .card-strip { background: linear-gradient(90deg, var(--secondary), #42A5F5); }
    .card--event .card-strip { background: linear-gradient(90deg, var(--primary), #66BB6A); }
    .card--program .card-strip { background: linear-gradient(90deg, var(--accent-deep), var(--accent)); }
    .card-body { padding: 20px 22px 24px; display: flex; flex-direction: column; flex: 1; }
    .card-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: var(--r-pill); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; margin-bottom: 12px; width: fit-content; }
    .card--announce .card-badge { background: var(--cat-announce-bg); color: var(--cat-announce); }
    .card--event .card-badge { background: var(--cat-event-bg); color: var(--cat-event); }
    .card--program .card-badge { background: var(--cat-program-bg); color: var(--cat-program); }
    .card h3 { margin: 0 0 10px; font-family: 'DM Serif Display', serif; font-size: 1.05rem; font-weight: 400; line-height: 1.4; color: var(--ink); transition: color var(--dur) var(--ease); }
    .card:hover h3 { color: var(--primary); }
    .card p { margin: 0 0 16px; font-size: 0.875rem; line-height: 1.7; color: var(--muted); flex: 1; display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .card-meta { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: auto; padding-top: 14px; border-top: 1px solid var(--border-soft); }
    .card-date { font-size: 0.76rem; font-weight: 600; color: var(--subtle); display: flex; align-items: center; gap: 5px; }
    .card-date svg { width: 12px; height: 12px; stroke: var(--subtle); }
    .card-link { font-size: 0.79rem; font-weight: 600; color: var(--primary); display: inline-flex; align-items: center; gap: 3px; transition: gap var(--dur) var(--ease); }
    .card:hover .card-link { gap: 6px; }
    .card-featured { grid-column: span 2; flex-direction: row; align-items: stretch; }
    .card-featured .card-img { width: 240px; flex-shrink: 0; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
    .card-featured .card-img-inner { font-size: 4rem; opacity: 0.35; position: absolute; }
    .card-featured .card-img-badge { font-family: 'DM Serif Display', serif; font-size: 1.1rem; color: rgba(255,255,255,0.9); text-align: center; z-index: 1; padding: 20px; }
    .card-featured .card-body { flex: 1; }
    .card-featured .card-strip { display: none; }
    .card-featured .card-img .strip-v { position: absolute; top: 0; left: 0; width: 4px; height: 100%; }
    .card-featured.card--announce .card-img .strip-v { background: linear-gradient(180deg, var(--secondary), #42A5F5); }
    .event-list { display: grid; gap: 16px; }
    .event-item { background: var(--surface); border: 1px solid var(--border-soft); border-radius: var(--r-md); box-shadow: var(--shadow-xs); display: flex; align-items: flex-start; gap: 20px; padding: 20px 22px; cursor: pointer; transition: transform var(--dur-s) var(--ease), box-shadow var(--dur-s) var(--ease), border-color var(--dur-s) var(--ease); }
    .event-item:hover { transform: translateX(4px); box-shadow: var(--shadow-md); border-color: var(--secondary-mid); }
    .event-date-badge { width: 58px; flex-shrink: 0; background: var(--secondary-pale); border: 1.5px solid rgba(13,71,161,0.15); border-radius: var(--r-md); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 8px 4px; }
    .event-date-badge .month { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--secondary-mid); }
    .event-date-badge .day { font-family: 'DM Serif Display', serif; font-size: 1.6rem; color: var(--secondary); line-height: 1.1; }
    .event-info { flex: 1; }
    .event-info h3 { font-family: 'DM Serif Display', serif; font-size: 1rem; font-weight: 400; line-height: 1.35; color: var(--ink); margin-bottom: 6px; }
    .event-item:hover .event-info h3 { color: var(--secondary); }
    .event-info p { font-size: 0.85rem; color: var(--muted); line-height: 1.6; margin-bottom: 10px; }
    .event-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .event-tag { padding: 3px 10px; border-radius: var(--r-pill); font-size: 0.69rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; background: var(--primary-pale); color: var(--primary); }
    .event-tag.blue { background: var(--secondary-pale); color: var(--secondary-mid); }
    .event-tag.gold { background: var(--accent-pale); color: var(--accent-deep); }
    .event-arrow { color: var(--subtle); font-size: 1.2rem; margin-top: 4px; flex-shrink: 0; transition: color var(--dur) var(--ease), transform var(--dur) var(--ease); }
    .event-item:hover .event-arrow { color: var(--secondary); transform: translateX(3px); }
    .cta-banner { margin: 64px 0 0; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: var(--r-xl); padding: 52px 60px; display: flex; align-items: center; justify-content: space-between; gap: 32px; box-shadow: var(--shadow-lg); position: relative; overflow: hidden; }
    .cta-banner::before { content: ''; position: absolute; width: 400px; height: 400px; border-radius: 50%; top: -150px; right: -80px; background: radial-gradient(circle, rgba(249,168,37,0.14) 0%, transparent 70%); }
    .cta-banner-text { position: relative; z-index: 1; }
    .cta-banner-text h2 { font-family: 'DM Serif Display', serif; font-size: 1.9rem; font-weight: 400; color: #fff; line-height: 1.2; margin-bottom: 10px; }
    .cta-banner-text p { font-size: 0.95rem; color: rgba(255,255,255,0.72); line-height: 1.7; }
    .cta-banner-action { position: relative; z-index: 1; flex-shrink: 0; }
    .btn-banner { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: var(--r-md); background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.5); color: #fff; font-size: 0.9rem; font-weight: 600; cursor: pointer; backdrop-filter: blur(4px); transition: background var(--dur) var(--ease), transform var(--dur) var(--ease); }
    .btn-banner:hover { background: rgba(255,255,255,0.25); color: #fff; transform: translateY(-2px); }
    .btn-banner-solid { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: var(--r-md); background: var(--accent); color: var(--ink); font-size: 0.9rem; font-weight: 700; border: none; cursor: pointer; box-shadow: 0 4px 16px var(--accent-glow); transition: background var(--dur) var(--ease), transform var(--dur) var(--ease); margin-left: 12px; }
    .btn-banner-solid:hover { background: #FFB300; color: var(--ink); transform: translateY(-2px); }
    .site-footer { background: var(--primary); color: rgba(255,255,255,0.55); padding: 40px 0; margin-top: 64px; }
    .site-footer .container { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
    .footer-brand { font-family: 'DM Serif Display', serif; font-size: 1rem; color: rgba(255,255,255,0.9); }
    .footer-brand span { display: block; font-family: 'DM Sans', sans-serif; font-size: 0.72rem; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; margin-top: 3px; opacity: 0.55; }
    .footer-links { display: flex; gap: 20px; flex-wrap: wrap; }
    .footer-links a { font-size: 0.82rem; color: rgba(255,255,255,0.55); transition: color var(--dur) var(--ease); }
    .footer-links a:hover { color: #fff; }
    .footer-copy { font-size: 0.78rem; }
    @media (max-width: 960px) { .card-grid { grid-template-columns: repeat(2, 1fr); gap: 18px; } .card-featured { grid-column: span 2; flex-direction: column; } .card-featured .card-img { width: 100%; height: 160px; } .cta-banner { flex-direction: column; padding: 40px 36px; text-align: center; } }
    @media (max-width: 640px) { .nav-toggle { display: flex; } .nav-links { display: none; } .topbar .container { height: 60px; } .hero { min-height: 78svh; padding: 60px 20px 90px; } .hero-emblem { width: 96px; height: 96px; padding: 4px; } .hero-emblem img { width: 100%; height: 100%; } .hero-title { font-size: clamp(1.9rem,9vw,2.8rem); } .hero-lead { font-size: 0.93rem; } .hero-cta { flex-direction: column; align-items: stretch; max-width: 340px; margin: 0 auto; } .btn-primary, .btn-ghost { width: 100%; justify-content: center; } .card-grid { grid-template-columns: 1fr; gap: 16px; } .card-featured { grid-column: span 1; } .stats-bar .container { flex-wrap: wrap; } .stat-item { flex: 1 1 50%; border-right: none; border-bottom: 1px solid var(--border-soft); } .cta-banner { padding: 32px 24px; } .cta-banner-text h2 { font-size: 1.5rem; } .site-footer .container { flex-direction: column; align-items: flex-start; gap: 16px; } }
    @media (max-width: 480px) { .brand-sub { display: none; } }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; } html { scroll-behavior: auto; } }
  </style>
</head>
<body>

  <!-- ------------------------------------------
       TOPBAR
  ------------------------------------------ -->
  <header class="topbar" id="topbar">
    <div class="container">
      <div class="brand-group">
        <div class="brand-logo-wrap">
          <img src="{{ asset('ssg-logo.png') }}" alt="SSG Logo" />
        </div>
        <div class="brand-text">
          <span class="brand-title">SSG Management System</span>
          <span class="brand-sub">CPSU · Hinoba-an Campus</span>
        </div>
      </div>

      <nav aria-label="Main navigation">
        <ul class="nav-links">
          <li><a href="#" class="active">Home</a></li>
          <li><a href="#announcements">Announcements</a></li>
          <li><a href="#events">Events</a></li>
          <li><a href="#programs">Programs</a></li>
          <li><a href="#about">About</a></li>
        </ul>
      </nav>

      <button class="btn-portal" onclick="window.location.href='{{ route('login') }}'">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M2 8h12M9 3l5 5-5 5"/>
        </svg>
        Open Portal
      </button>

      <button class="nav-toggle" aria-label="Toggle navigation">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- ------------------------------------------
       HERO
  ------------------------------------------ -->
  <section class="hero" aria-label="Welcome hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-overlay" aria-hidden="true"></div>
    <div class="hero-deco hero-deco--a" aria-hidden="true"></div>
    <div class="hero-deco hero-deco--b" aria-hidden="true"></div>

    <div class="hero-emblem">
      <img src="{{ asset('ssg-logo.png') }}" alt="Supreme Student Government Logo" />
    </div>

    <div class="eyebrow">
      <span class="pulse"></span>
      Supreme Student Government
    </div>

    <h1 class="hero-title">
      Empowering<br/><em>Student Voice</em><br/>& Leadership
    </h1>

    <p class="hero-lead">
      The official management portal of the Supreme Student Government of
      Central Philippines State University — Hinoba-an Campus. Stay connected
      with announcements, events, and programs designed for you.
    </p>

    <div class="hero-cta">
      <button class="btn-primary" onclick="window.location.href='{{ route('login') }}'">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="width:16px;height:16px">
          <rect x="1" y="1" width="6" height="6" rx="1.5"/>
          <rect x="9" y="1" width="6" height="6" rx="1.5"/>
          <rect x="1" y="9" width="6" height="6" rx="1.5"/>
          <rect x="9" y="9" width="6" height="6" rx="1.5"/>
        </svg>
        Open Student Portal
      </button>
      <button class="btn-ghost">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:16px;height:16px">
          <circle cx="8" cy="8" r="7"/><path d="M8 5v3l2 2"/>
        </svg>
        Upcoming Events
      </button>
    </div>

    <div class="scroll-hint" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M12 5v14M6 13l6 6 6-6"/>
      </svg>
      Scroll to explore
    </div>
  </section>

  <!-- ... remainder of HTML in original template preserved ... -->
  <script>
    /* Subtle topbar scroll enhancement */
    const topbar = document.getElementById('topbar');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 60) {
        topbar.style.background = 'rgba(255,255,255,0.97)';
        topbar.style.boxShadow = '0 2px 16px rgba(27,94,32,0.12)';
      } else {
        topbar.style.background = 'rgba(255,255,255,0.94)';
        topbar.style.boxShadow = '0 1px 10px rgba(27,94,32,0.08)';
      }
    });

    /* Mobile nav toggle */
    document.querySelector('.nav-toggle').addEventListener('click', function () {
      const nav = document.querySelector('.nav-links');
      const isOpen = nav.style.display === 'flex';
      if (isOpen) {
        nav.style.display = '';
      } else {
        nav.style.cssText = 'display:flex;flex-direction:column;position:absolute;top:60px;left:0;right:0;background:#fff;border-bottom:1px solid rgba(0,0,0,0.07);padding:12px 20px 16px;box-shadow:0 8px 28px rgba(27,94,32,0.12);z-index:99;gap:2px;';
        nav.querySelectorAll('a').forEach(a => { a.style.cssText = 'display:block;padding:10px 16px;font-size:0.95rem;'; });
      }
    });

    /* Animate stats on scroll */
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.12 });

    document.querySelectorAll('.card, .event-item, .stat-item').forEach(el => {
      el.style.cssText += 'opacity:0;transform:translateY(18px);transition:opacity 0.5s ease, transform 0.5s ease;';
      observer.observe(el);
    });
  </script>
</body>
</html>

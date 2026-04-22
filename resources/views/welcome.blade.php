<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSG Management System | CPSU Hinoba-an Campus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/welcome.css" rel="stylesheet">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to content</a>
    <header class="public-nav">
        <div class="container public-nav-inner">
            <a href="{{ route('welcome') }}" class="nav-brand" aria-label="SSG Management System home">
                <div class="nav-brand-mark">
                    <img src="/ssg-logo.png" alt="CPSU SSG logo">
                </div>
                <div>
                    <div class="nav-brand-name">SSG Management System</div>
                    <div class="nav-brand-sub">CPSU Hinoba-an Campus</div>
                </div>
            </a>

            <nav style="position: relative;">
                <button class="nav-toggle" type="button" id="navToggle" aria-label="Toggle navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="nav-links" id="navLinks">
                    <a href="#home" class="nav-link">Home</a>
                    <a href="#announcements" class="nav-link">Announcements</a>
                    <a href="#events" class="nav-link">Events</a>
                    <a href="#about" class="nav-link">About</a>
                </div>
                <a href="{{ route('login') }}" class="nav-btn">Sign In</a>
            </nav>
        </div>
    </header>

    <main id="main-content">
        <section class="hero" id="home">
            <div class="container hero-inner">
                <div class="hero-copy">
                    <span class="ssg-badge"><span class="ssg-badge-dot"></span> Supreme Student Government</span>
                    <h1 class="hero-title">Empowering <span class="hero-title__emphasis">Student Voice</span>.</h1>
                    <h1 class="hero-title">& <span class="hero-title__emphasis">Leadership</span>.</h1>
                    <p class="hero-body">The official management portal of the Supreme Student Government at Central Philippines State University – Hinoba-an Campus. Stay updated with announcements, events, and student support through a single public-facing portal for official notices, event schedules, and attendance management across CPSU Hinoba-an..</p>
                    <div class="hero-highlights">
                        <div class="hero-highlight">
                            <strong>Public</strong>
                            <span>Announcements and event schedules are accessible to everyone on any device.</span>
                        </div>
                        <div class="hero-highlight">
                            <strong>Full access after login</strong>
                            <span>Students, officers, move into role-based workspaces.</span>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="{{ route('login') }}" class="btn-gold">Open Portal</a>
                        <a href="#announcements" class="btn-ghost">View Public Updates</a>
                    </div>
                    <div class="hero-proof">
                        <span>Live public access via ngrok</span>
                        <span>QR attendance ready</span>
                        <span>Concern reply workflow active</span>
                    </div>
                </div>

                <aside class="hero-visual" aria-label="Today's campus snapshot">
                    <div class="hero-orb hero-orb-one"></div>
                    <div class="hero-orb hero-orb-two"></div>
                    <div class="hero-summary">
                        <div class="hero-summary-head">
                            <span class="summary-kicker">What's On Today</span>
                        </div>
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-value">{{ $stats['students'] ?? 0 }}</div>
                                <div class="stat-name">Active Students</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $stats['events'] ?? 0 }}</div>
                                <div class="stat-name">Upcoming Events</div>
                            </div>
                        </div>
                        <div class="hero-summary-note">
                            <p>The portal delivers role-based access for all campus activities, announcements, and event management.</p>
                        </div>
                        <div class="hero-summary-footer">
                            <small>Live campus operations, available anytime, anywhere, on all devices.</small>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="signal-band" aria-label="Platform highlights">
            <div class="container signal-grid">
                <article class="signal-item">
                    <span class="signal-index">01</span>
                    <div>
                        <strong>Official announcement feed</strong>
                        <p>Public visitors see current notices instantly, while students receive full role-targeted content after sign-in.</p>
                    </div>
                </article>
                <article class="signal-item">
                    <span class="signal-index">02</span>
                    <div>
                        <strong>Attendance workflows with QR</strong>
                        <p>Attendance-required events are prepared for secure QR issuance, scanning, and validation.</p>
                    </div>
                </article>
                <article class="signal-item">
                    <span class="signal-index">03</span>
                    <div>
                        <strong>Student concerns with reply tracking</strong>
                        <p>Students submit concerns in one place, and officers or admins manage replies through the portal.</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="public-section" id="announcements">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="section-kicker">Official Notices</span>
                        <h2 class="section-title">Latest announcements from the SSG.</h2>
                    </div>
                    <p class="section-copy">The public feed shows currently published campus-wide updates from the student government. Students see role-targeted content after sign-in.</p>
                </div>

                <div class="content-grid">
                    @forelse ($announcements->take(3) as $announcement)
                        <article class="feed-card">
                            <div class="feed-card-body">
                                <span class="feed-chip">Published</span>
                                <h3>{{ $announcement->title }}</h3>
                                <p>{{ $announcement->description ?: \Illuminate\Support\Str::limit($announcement->message, 140) }}</p>
                                <div class="feed-meta">
                                    <span>Published {{ $announcement->sent_at?->format('M d, Y') ?? $announcement->created_at?->format('M d, Y') }}</span>
                                    <span>Campus notice</span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="feed-card">
                            <div class="feed-card-body">
                                <span class="feed-chip">No notices</span>
                                <h3>No published announcements yet.</h3>
                                <p>New public notices will appear here once they are sent through the system.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="public-section" id="events" style="padding-top: 0;">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="section-kicker">Upcoming Events</span>
                        <h2 class="section-title">Public event schedules and attendance-ready activities.</h2>
                    </div>
                    <p class="section-copy">Students can register attendance through secure QR workflows in the portal. Officers and admins manage publishing, scheduling, and monitoring from their dashboards.</p>
                </div>

                <div class="content-grid">
                    @forelse ($events->take(3) as $event)
                        <article class="feed-card">
                            <div class="feed-card-body">
                                <span class="feed-chip">{{ $event->attendance_required ? 'Attendance Required' : 'Open Event' }}</span>
                                <h3>{{ $event->event_title }}</h3>
                                <p>{{ $event->event_description }}</p>
                                <div class="feed-meta">
                                    <span>{{ optional($event->event_date)->format('M d, Y') ?? $event->event_date }} {{ substr((string) $event->event_time, 0, 5) }}</span>
                                    <span>{{ $event->location }}</span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="feed-card">
                            <div class="feed-card-body">
                                <span class="feed-chip">No events</span>
                                <h3>No upcoming events yet.</h3>
                                <p>Once officers publish new event schedules, they will appear in this section automatically.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="public-section portal-band" id="about">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="section-kicker">Role-Based Workspaces</span>
                        <h2 class="section-title" style="color: var(--cream);">Designed for public access, student action, officer operations, and admin control.</h2>
                    </div>
                    <p class="section-copy">Each portal surface is built around a different operating need: public information, student participation, officer publishing, and administrative oversight.</p>
                </div>

                <div class="portal-grid">
                    <div class="portal-list">
                        <div class="portal-item">
                            <strong>Public</strong>
                            <span>Open campus updates, public events, and the front-door experience for the SSG system.</span>
                        </div>
                        <div class="portal-item">
                            <strong>Student</strong>
                            <span>Announcements, events, profile updates, attendance presentation, and concern submission with tracked replies.</span>
                        </div>
                        <div class="portal-item">
                            <strong>Officer</strong>
                            <span>Announcement and event management, concern handling, and attendance monitoring for operational workflows.</span>
                        </div>
                        <div class="portal-item">
                            <strong>Admin</strong>
                            <span>Account administration, reporting, attendance oversight, and full management access for the system.</span>
                        </div>
                    </div>

                    <div class="portal-card">
                        <h3>What the system already handles</h3>
                        <p>Database-backed content delivery, role-aware routing, scheduled announcement processing, concern replies, and secure attendance token flows.</p>
                        <div class="portal-card-grid">
                            <div>
                                <strong>Announcements</strong>
                                <span>Draft, scheduled, and published states</span>
                            </div>
                            <div>
                                <strong>Concerns</strong>
                                <span>Student submission and admin reply visibility</span>
                            </div>
                            <div>
                                <strong>Attendance</strong>
                                <span>QR-based attendance checks and reporting</span>
                            </div>
                            <div>
                                <strong>Accounts</strong>
                                <span>Student and officer records with role controls</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-band">
            <div class="container">
                <div class="cta-panel">
                    <div>
                        <span class="section-kicker">Portal Access</span>
                        <h2 class="section-title">Ready to continue into your workspace?</h2>
                        <p>Students, officers, and administrators use the same system with separate dashboards, permissions, and database-backed actions.</p>
                    </div>
                    <a href="{{ route('login') }}" class="btn-gold">Sign In to Portal</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-title">SSG Management System</div>
                    <div class="footer-contact">
                        <span>Central Philippines State University</span>
                        <span>Hinoba-an Campus</span>
                    </div>
                </div>
                <div class="footer-section">
                    <div class="footer-year">Academic Year {{ now()->format('Y') }}</div>
                </div>
                <div class="footer-section">
                    <div class="footer-copy">© {{ now()->format('Y') }} Supreme Student Government. All rights reserved.</div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        (() => {
            const navToggle = document.getElementById('navToggle');
            const navLinks = document.getElementById('navLinks');

            navToggle?.addEventListener('click', () => {
                navLinks?.classList.toggle('open');
            });

            navLinks?.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => navLinks.classList.remove('open'));
            });
        })();
    </script>
</body>
</html>

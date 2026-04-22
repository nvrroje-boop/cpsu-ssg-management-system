#!/usr/bin/env python3
import re

# Read the file
with open(r'c:\Users\gesto\OneDrive\Desktop\ssg-management-system\resources\views\welcome.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Define the replacement for announcements section
announcements_replacement = '''        <div class="card-grid">
          @forelse($announcements as $announcement)
            <article class="card card--announce {{ $loop->first ? 'card-featured' : '' }}">
              @if($loop->first)
                <div class="card-img">
                  <div class="card-img-inner">📢</div>
                  <div class="card-img-badge">
                    SSG<br/>Official<br/>Notice
                  </div>
                  <div class="strip-v"></div>
                </div>
              @else
                <div class="card-strip"></div>
              @endif
              <div class="card-body">
                <span class="card-badge">Announcement</span>
                <h3>{{ $announcement->title }}</h3>
                <p>{{ Illuminate\Support\Str::words($announcement->description, 30) }}</p>
                <div class="card-meta">
                  <span class="card-date">
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="1" y="3" width="14" height="12" rx="2"/><path d="M1 7h14M5 1v4M11 1v4"/></svg>
                    {{ $announcement->created_at->format('F j, Y') }}
                  </span>
                  <span class="card-link">Read more →</span>
                </div>
              </div>
            </article>
          @empty
            <article class="card card--announce card-featured">
              <div class="card-img">
                <div class="card-img-inner">📢</div>
                <div class="card-img-badge">
                  SSG<br/>Portal
                </div>
                <div class="strip-v"></div>
              </div>
              <div class="card-body">
                <span class="card-badge">Information</span>
                <h3>SSG Management System</h3>
                <p>Welcome to the official student government management portal. Stay connected with announcements, events, and campus updates.</p>
                <div class="card-meta">
                  <span class="card-date">
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="1" y="3" width="14" height="12" rx="2"/><path d="M1 7h14M5 1v4M11 1v4"/></svg>
                    Welcome
                  </span>
                  <span class="card-link">Login →</span>
                </div>
              </div>
            </article>
          @endforelse
        </div>'''

# Replace announcements - use regex to safely match the section
pattern = r'        <div class="card-grid">\s+<article class="card card--announce card-featured">.*?        </div>\s+      </section>'
content = re.sub(pattern, announcements_replacement + '\n      </section>', content, count=1, flags=re.DOTALL)

# Define replacement for events section
events_replacement = '''        <div class="event-list">
          @forelse($events as $event)
            <article class="event-item">
              <div class="event-date-badge">
                <span class="month">{{ $event->event_date->format('M') }}</span>
                <span class="day">{{ $event->event_date->format('d') }}</span>
              </div>
              <div class="event-info">
                <h3>{{ $event->title }}</h3>
                <p>{{ Illuminate\Support\Str::words($event->description, 25) }}</p>
                <div class="event-tags">
                  <span class="event-tag">{{ $event->category ?? 'Event' }}</span>
                  @if($event->visibility)
                    <span class="event-tag blue">{{ ucfirst($event->visibility) }}</span>
                  @endif
                  @if($event->event_time)
                    <span class="event-tag gold">{{ $event->event_time }} – {{ $event->location ?? 'TBA' }}</span>
                  @endif
                </div>
              </div>
              <span class="event-arrow">→</span>
            </article>
          @empty
            <article class="event-item">
              <div class="event-date-badge">
                <span class="month">Coming</span>
                <span class="day">Soon</span>
              </div>
              <div class="event-info">
                <h3>Stay tuned for upcoming events!</h3>
                <p>Check back soon for announcements of new campus events and activities organized by the Student Government.</p>
                <div class="event-tags">
                  <span class="event-tag">Info</span>
                  <span class="event-tag blue">All Students</span>
                </div>
              </div>
              <span class="event-arrow">→</span>
            </article>
          @endforelse
        </div>'''

# Replace events using regex
event_pattern = r'        <div class="event-list">\s+<article class="event-item">.*?        </div>\s+      </section>'
content = re.sub(event_pattern, events_replacement + '\n      </section>', content, count=1, flags=re.DOTALL)

# Write back
with open(r'c:\Users\gesto\OneDrive\Desktop\ssg-management-system\resources\views\welcome.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Replacement completed successfully!")

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — SSG Management System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet" />
  <style>
    /* --- TOKENS --- */
    :root {
      --primary:         #1B5E20;
      --primary-mid:     #2E7D32;
      --primary-light:   #388E3C;
      --primary-pale:    #E8F5E9;
      --primary-glow:    rgba(27, 94, 32, 0.20);

      --secondary:       #0D47A1;
      --secondary-mid:   #1565C0;
      --secondary-pale:  #E3F2FD;

      --accent:          #F9A825;
      --accent-deep:     #E65100;
      --accent-pale:     #FFF8E1;

      --sb-bg:           #132B14;
      --sb-bg-hover:     #1B3D1C;
      --sb-bg-active:    #1B5E20;
      --sb-text:         rgba(255,255,255,0.62);
      --sb-text-active:  #FFFFFF;
      --sb-border:       rgba(255,255,255,0.07);
      --sb-width:        248px;
      --sb-collapsed:    68px;

      /* ... omitted for brevity ... */
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem; line-height: 1.6;
      color: var(--ink); background: var(--surface-dim);
      display: flex; min-height: 100svh;
      overflow-x: hidden;
    }
    img { display: block; max-width: 100%; }
    a { color: inherit; text-decoration: none; }
    :focus-visible { outline: 2px solid var(--primary); outline-offset: 3px; border-radius: var(--r-sm); }

    .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sb-width); background: var(--sb-bg); display: flex; flex-direction: column; z-index: 300; transition: width var(--dur) var(--ease), transform var(--dur) var(--ease); overflow: hidden; }

    /* ... the rest of CSS as provided ... */
  </style>
</head>
<body>
  <!-- INSERT full HTML content for admin dashboard from your data (same as first template) -->
  <p>Admin UI placeholder</p>
</body>
</html>

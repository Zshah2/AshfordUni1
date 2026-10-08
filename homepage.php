<?php
/** @var array $app */
?>
<section class="relative isolate overflow-hidden bg-indigo-100 dark:bg-[#0a0f1f]">
  <div class="relative min-h-[72vh]">
    <img
      src="<?= htmlspecialchars(url('/assets/img/campus-hero.jpg')) ?>"
      alt="Ashford campus"
      class="absolute inset-0 h-full w-full object-cover"
      loading="eager"
      decoding="async"
    />
    <div class="absolute inset-0 bg-gradient-to-b from-white/70 via-indigo-200/50 to-indigo-100 dark:from-indigo-950/80 dark:via-fuchsia-950/45 dark:to-[#0a0f1f]"></div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_90%_60%_at_50%_-10%,rgba(232,121,249,0.22),transparent_55%)]"></div>

    <div class="relative mx-auto flex min-h-[72vh] max-w-6xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6">
      <p class="text-xs font-semibold tracking-[0.22em] text-indigo-700 dark:text-cyan-300">ASHFORD UNIVERSITY</p>
      <h1 class="mt-4 bg-gradient-to-r from-indigo-950 via-violet-800 to-fuchsia-700 bg-clip-text font-serif text-6xl font-semibold tracking-tight text-transparent dark:from-white dark:via-cyan-100 dark:to-fuchsia-200 sm:text-7xl">
        Ashford
      </h1>
      <p class="mt-5 max-w-2xl text-base text-slate-700 dark:text-slate-200 sm:text-lg">
        <?= htmlspecialchars($app['site']['description']) ?>
      </p>
      <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
        <a href="<?= htmlspecialchars(nav_url($app['cta']['primary']['href'])) ?>" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-fuchsia-600 to-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-fuchsia-900/40 transition hover:from-fuchsia-500 hover:to-violet-500">
          <?= htmlspecialchars($app['cta']['primary']['label']) ?>
        </a>
        <a href="#visit" class="inline-flex items-center justify-center rounded-xl border border-cyan-400/40 bg-cyan-950/35 px-6 py-3 text-sm font-semibold text-cyan-50 backdrop-blur-sm transition hover:border-cyan-300/60 hover:bg-cyan-900/45">
          Visit campus
        </a>
      </div>
    </div>
  </div>

  <div class="border-t border-indigo-200/80 bg-white/80 dark:border-fuchsia-500/20 dark:bg-[#0a0f1f]/80">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-center gap-3 px-4 py-4 sm:px-6">
      <a class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white" href="/faculty/master-schedule.php">Academic calendar</a>
      <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-white" href="/faculty/master-schedule.php">Fall 2026 schedule</a>
      <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-white" href="/faculty/master-schedule.php">Spring 2027 schedule</a>
      <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-white" href="/student/catalog.php">University catalog</a>
      <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-white" href="/login.php">Login</a>
    </div>
  </div>
  <a href="#explore" class="flex items-center justify-center gap-2 border-t border-fuchsia-500/30 bg-gradient-to-r from-violet-600 via-fuchsia-500 to-amber-400 px-4 py-4 text-sm font-semibold text-white shadow-inner shadow-black/20 transition hover:brightness-110">
    Explore Ashford
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </a>
</section>

<section id="visit" class="nb-home-band border-t border-slate-200 bg-gradient-to-b from-indigo-50 via-white to-slate-50 dark:border-fuchsia-500/15 dark:from-indigo-950/60 dark:via-[#0a0f1f] dark:to-[#0a0f1f]">
  <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
    <div class="grid gap-10 lg:grid-cols-12 lg:items-start">
      <div class="lg:col-span-5">
        <div class="nb-home-accent text-sm font-semibold text-indigo-600 dark:text-cyan-300">Campus life</div>
        <h2 class="nb-home-heading mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">A place to belong—and grow</h2>
        <p class="nb-home-body mt-3 text-sm text-slate-600 dark:text-slate-300">
          From hands-on labs to student clubs and career coaching, Ashford is built around support and momentum.
        </p>
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          <div class="nb-home-card rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 dark:border-fuchsia-500/25 dark:bg-gradient-to-br dark:from-fuchsia-950/35 dark:to-violet-950/20 dark:hover:border-cyan-400/35">
            <div class="text-base font-semibold text-slate-900 dark:text-white">Clubs &amp; communities</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">120+ organizations across interests and majors.</div>
          </div>
          <div class="nb-home-card rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 dark:border-fuchsia-500/25 dark:bg-gradient-to-br dark:from-fuchsia-950/35 dark:to-violet-950/20 dark:hover:border-cyan-400/35">
            <div class="text-base font-semibold text-slate-900 dark:text-white">Research &amp; labs</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Project-based learning with modern facilities.</div>
          </div>
          <div class="nb-home-card rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 dark:border-fuchsia-500/25 dark:bg-gradient-to-br dark:from-fuchsia-950/35 dark:to-violet-950/20 dark:hover:border-cyan-400/35">
            <div class="text-base font-semibold text-slate-900 dark:text-white">Career services</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Internships, advising, and interview prep.</div>
          </div>
          <div class="nb-home-card rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 dark:border-fuchsia-500/25 dark:bg-gradient-to-br dark:from-fuchsia-950/35 dark:to-violet-950/20 dark:hover:border-cyan-400/35">
            <div class="text-base font-semibold text-slate-900 dark:text-white">Student support</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Tutoring, accessibility services, and wellness resources.</div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-7">
        <div class="nb-home-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:border-fuchsia-500/30 dark:bg-gradient-to-br dark:from-violet-950/40 dark:to-fuchsia-950/25 dark:shadow-fuchsia-950/20">
          <img
            src="<?= htmlspecialchars(url('/assets/img/students-walking.jpg')) ?>"
            alt="Students walking on campus"
            class="h-56 w-full object-cover object-center sm:h-72"
            loading="lazy"
            decoding="async"
          />
          <div class="nb-home-visit-panel p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <div class="nb-home-visit-title text-sm font-semibold text-slate-900 dark:text-white">Plan your visit</div>
                <div class="nb-home-body mt-1 text-sm text-slate-600 dark:text-slate-300">Tours, info sessions, and advisor meetings.</div>
              </div>
              <a href="#contact" class="inline-flex shrink-0 items-center justify-center rounded-2xl bg-gradient-to-r from-fuchsia-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-fuchsia-900/40 transition hover:from-fuchsia-500 hover:to-violet-500">
                Contact admissions
              </a>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
              <div class="nb-home-stat rounded-3xl border border-indigo-200 bg-indigo-50 p-4 dark:border-cyan-500/25 dark:bg-black/25">
                <div class="nb-home-stat-label text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-cyan-300/80">Where</div>
                <div class="nb-home-stat-value mt-1 text-base font-semibold text-slate-900 dark:text-white">Ashford Campus</div>
                <div class="nb-home-body mt-1 text-sm text-slate-600 dark:text-slate-300">Student center check-in</div>
              </div>
              <div class="nb-home-stat rounded-3xl border border-indigo-200 bg-indigo-50 p-4 dark:border-cyan-500/25 dark:bg-black/25">
                <div class="nb-home-stat-label text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-cyan-300/80">What to bring</div>
                <div class="nb-home-stat-value mt-1 text-base font-semibold text-slate-900 dark:text-white">Questions + curiosity</div>
                <div class="nb-home-body mt-1 text-sm text-slate-600 dark:text-slate-300">We’ll handle the rest.</div>
              </div>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
              <div class="nb-home-stat rounded-3xl border border-indigo-200 bg-indigo-50 p-4 dark:border-fuchsia-500/20 dark:bg-black/25">
                <div class="nb-home-stat-label text-xs text-violet-700 dark:text-fuchsia-300/80">Student-to-faculty</div>
                <div class="nb-home-stat-value mt-1 text-lg font-semibold text-indigo-900 dark:text-cyan-100">12:1</div>
              </div>
              <div class="nb-home-stat rounded-3xl border border-indigo-200 bg-indigo-50 p-4 dark:border-fuchsia-500/20 dark:bg-black/25">
                <div class="nb-home-stat-label text-xs text-violet-700 dark:text-fuchsia-300/80">Average class size</div>
                <div class="nb-home-stat-value mt-1 text-lg font-semibold text-indigo-900 dark:text-cyan-100">22</div>
              </div>
              <div class="nb-home-stat rounded-3xl border border-indigo-200 bg-indigo-50 p-4 dark:border-fuchsia-500/20 dark:bg-black/25">
                <div class="nb-home-stat-label text-xs text-violet-700 dark:text-fuchsia-300/80">Internship partners</div>
                <div class="nb-home-stat-value mt-1 text-lg font-semibold text-indigo-900 dark:text-cyan-100">300+</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="programs" class="nb-home-band border-t border-slate-200 bg-white dark:border-fuchsia-500/15 dark:bg-[#0c1028]">
  <div id="explore" class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <div class="nb-home-accent text-sm font-semibold text-violet-700 dark:text-fuchsia-300">Programs</div>
        <h2 class="nb-home-heading mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Choose a path that fits your goals</h2>
        <p class="nb-home-body mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-300">A balanced mix of fundamentals and real-world projects.</p>
      </div>
      <a href="#admissions" class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-800 dark:text-cyan-300 dark:hover:text-cyan-200">See admissions →</a>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <?php
        $cards = [
          ['title' => 'Computer Science', 'desc' => 'Software engineering, data, and systems.'],
          ['title' => 'Business Administration', 'desc' => 'Operations, leadership, and entrepreneurship.'],
          ['title' => 'Health Sciences', 'desc' => 'Hands-on labs and community partnerships.'],
          ['title' => 'Engineering', 'desc' => 'Design, build, test—industry-ready skills.'],
          ['title' => 'Arts & Media', 'desc' => 'Storytelling, design thinking, production.'],
          ['title' => 'Education', 'desc' => 'Modern teaching methods and field practice.'],
        ];
      ?>
      <?php foreach ($cards as $c): ?>
        <div class="nb-home-card group rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 dark:border-violet-500/30 dark:bg-gradient-to-br dark:from-violet-950/50 dark:to-fuchsia-950/25 dark:hover:border-cyan-400/40 dark:hover:from-violet-900/55">
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="text-base font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($c['title']) ?></div>
              <div class="mt-1 text-sm text-slate-600 dark:text-slate-300"><?= htmlspecialchars($c['desc']) ?></div>
            </div>
            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200 dark:bg-fuchsia-600/25 dark:text-fuchsia-200 dark:ring-fuchsia-400/40">↗</span>
          </div>
          <div class="mt-4 text-sm font-semibold text-indigo-600 group-hover:text-indigo-800 dark:text-cyan-300 dark:group-hover:text-cyan-200">Learn more</div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="departments" class="nb-home-band border-t border-slate-200 bg-slate-50 dark:border-cyan-500/10 dark:bg-gradient-to-b dark:from-[#070b18] dark:to-indigo-950/35">
  <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
    <div class="grid gap-10 lg:grid-cols-12">
      <div class="lg:col-span-5">
        <div class="nb-home-accent text-sm font-semibold text-cyan-700 dark:text-cyan-300">Departments</div>
        <h2 class="nb-home-heading mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Support from day one</h2>
        <p class="nb-home-body mt-3 text-sm text-slate-600 dark:text-slate-300">
          Advising, tutoring, labs, and student services—organized so you can focus on learning.
        </p>
        <div class="mt-6 grid gap-3">
          <div class="nb-home-card-muted rounded-2xl border border-slate-200 bg-white p-4 dark:border-cyan-500/20 dark:bg-cyan-950/15">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">Academic advising</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Degree plans, scheduling, and goal tracking.</div>
          </div>
          <div class="nb-home-card-muted rounded-2xl border border-slate-200 bg-white p-4 dark:border-cyan-500/20 dark:bg-cyan-950/15">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">Student success center</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Tutoring, writing lab, and study groups.</div>
          </div>
          <div class="nb-home-card-muted rounded-2xl border border-slate-200 bg-white p-4 dark:border-cyan-500/20 dark:bg-cyan-950/15">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">Career services</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Internships, resume review, interview prep.</div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-7">
        <div class="nb-home-panel rounded-3xl border border-slate-200 bg-indigo-50 p-6 shadow-lg dark:border-fuchsia-500/25 dark:bg-gradient-to-br dark:from-fuchsia-600/15 dark:via-violet-600/10 dark:to-cyan-500/10 dark:shadow-fuchsia-950/25">
          <div class="flex items-center justify-between gap-4">
            <div>
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Student experience</div>
              <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Resources that meet you where you are—and push you forward.</div>
            </div>
            <span class="rounded-full bg-fuchsia-500/20 px-3 py-1 text-xs font-semibold text-fuchsia-100 ring-1 ring-fuchsia-400/40">Support</span>
          </div>

          <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="nb-home-card-muted rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-black/30">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Mentorship</div>
              <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Faculty office hours, peer tutors, and guided projects.</div>
            </div>
            <div class="nb-home-card-muted rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-black/30">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Hands-on labs</div>
              <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Equipment and studio spaces built for real practice.</div>
            </div>
            <div class="nb-home-card-muted rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-black/30">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Career pathways</div>
              <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Internships, employer partners, and interview coaching.</div>
            </div>
            <div class="nb-home-card-muted rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-black/30">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Campus life</div>
              <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Clubs, leadership programs, and community events.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="about" class="nb-home-band border-t border-slate-200 bg-white dark:border-fuchsia-500/15 dark:bg-gradient-to-b dark:from-violet-950/35 dark:to-[#0a0f1f]">
  <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
    <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
      <div class="lg:col-span-6">
        <div class="nb-home-accent text-sm font-semibold text-violet-700 dark:text-fuchsia-300">About</div>
        <h2 class="nb-home-heading mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Built for serious learners</h2>
        <p class="nb-home-body mt-3 text-sm text-slate-600 dark:text-slate-300">
          Ashford blends rigorous academics with practical experience—small classes, modern labs, and advisors who know your name.
        </p>
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          <div class="nb-home-card rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-fuchsia-500/25 dark:bg-fuchsia-950/20">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">Mission</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">Prepare students to lead with clarity, skill, and integrity.</div>
          </div>
          <div class="nb-home-card rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-fuchsia-500/25 dark:bg-fuchsia-950/20">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">Community</div>
            <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">A welcoming campus where collaboration beats competition.</div>
          </div>
        </div>
      </div>
      <div class="lg:col-span-6">
        <div class="overflow-hidden rounded-3xl border border-fuchsia-500/30 bg-gradient-to-br from-violet-950/40 to-fuchsia-950/20">
          <img
            src="<?= htmlspecialchars(url('/assets/img/library.jpg')) ?>"
            alt="Ashford library"
            class="h-56 w-full object-cover sm:h-64"
            loading="lazy"
            decoding="async"
          />
          <div class="p-6">
            <div class="nb-home-heading text-sm font-semibold text-slate-900 dark:text-white">Visit the quad</div>
            <div class="nb-home-body mt-1 text-sm text-slate-600 dark:text-slate-300">Walk the campus, meet faculty, and picture your next chapter.</div>
            <div class="mt-4 flex flex-wrap gap-3">
              <a href="#visit" class="rounded-2xl bg-gradient-to-r from-fuchsia-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-fuchsia-900/35 hover:from-fuchsia-500 hover:to-violet-500">Campus tour</a>
              <a href="#contact" class="nb-home-btn-secondary rounded-2xl border border-cyan-300 bg-cyan-50 px-4 py-2 text-sm font-semibold shadow-sm hover:bg-cyan-100 dark:border-cyan-400/35 dark:bg-cyan-950/30 dark:hover:border-cyan-300/55 dark:hover:bg-cyan-900/40">Talk to admissions</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="admissions" class="nb-home-band border-t border-slate-200 bg-indigo-50 dark:border-fuchsia-500/15 dark:bg-[#0c1028]">
  <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
    <div class="nb-home-panel rounded-3xl border border-violet-200 bg-gradient-to-r from-violet-100 via-indigo-50 to-cyan-50 p-7 sm:p-10 dark:border-fuchsia-500/25 dark:from-fuchsia-600/20 dark:via-violet-600/15 dark:to-cyan-500/15">
      <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
        <div class="lg:col-span-7">
          <div class="nb-home-accent text-sm font-semibold text-amber-800 dark:text-amber-200">Admissions</div>
          <h2 class="nb-home-heading mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Apply with confidence</h2>
          <p class="nb-home-body mt-3 text-sm text-slate-600 dark:text-slate-300">
            We’ll connect this CTA to a real form once MySQL is ready. For now, the homepage nails the look and structure.
          </p>
          <ul class="mt-5 grid gap-2 text-sm text-slate-700 dark:text-slate-200 sm:grid-cols-2">
            <li class="flex items-center gap-2"><span class="nb-home-check">✓</span> Clear requirements</li>
            <li class="flex items-center gap-2"><span class="nb-home-check">✓</span> Fast response</li>
            <li class="flex items-center gap-2"><span class="nb-home-check">✓</span> Financial aid guidance</li>
            <li class="flex items-center gap-2"><span class="nb-home-check">✓</span> Campus visit options</li>
          </ul>
        </div>
        <div class="lg:col-span-5">
          <div class="nb-home-card rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur-sm dark:border-cyan-500/20 dark:bg-black/35">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">Quick actions</div>
            <div class="mt-4 grid gap-3">
              <a class="rounded-2xl bg-gradient-to-r from-fuchsia-600 to-violet-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-fuchsia-900/35 hover:from-fuchsia-500 hover:to-violet-500" href="#">
                Start application (next step)
              </a>
              <a class="nb-home-btn-secondary rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-center text-sm font-semibold hover:bg-cyan-100 dark:border-cyan-500/25 dark:bg-cyan-950/25 dark:hover:bg-cyan-900/35" href="#news">
                View campus updates
              </a>
              <a class="rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 text-center text-sm font-semibold text-violet-900 hover:bg-violet-100 dark:border-fuchsia-500/25 dark:bg-fuchsia-950/20 dark:text-slate-100 dark:hover:bg-fuchsia-900/30" href="#contact">
                Contact admissions
              </a>
            </div>
            <div class="nb-home-body mt-4 text-xs text-slate-500 dark:text-slate-400">Tip: Later we’ll connect your CSV to MySQL and power real data here.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="events" class="nb-home-band border-t border-slate-200 bg-white dark:border-cyan-500/10 dark:bg-gradient-to-b dark:from-[#070b18] dark:to-[#0a0f1f]">
  <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <div class="nb-home-accent text-sm font-semibold text-amber-700 dark:text-amber-300">Events</div>
        <h2 class="nb-home-heading mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">On campus &amp; online</h2>
        <p class="nb-home-body mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-300">A few upcoming moments to explore Ashford.</p>
      </div>
      <a href="#visit" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 dark:text-slate-200 dark:hover:text-white">Plan a visit →</a>
    </div>

    <div class="mt-8 grid gap-4 lg:grid-cols-3">
      <?php
        $events = [
          ['title' => 'Campus tour + faculty meet-up', 'when' => 'May 6, 2026 · 10:00am', 'where' => 'Student Center'],
          ['title' => 'Financial aid night (virtual)', 'when' => 'May 12, 2026 · 6:30pm', 'where' => 'Online'],
          ['title' => 'Summer preview: labs open house', 'when' => 'Jun 2, 2026 · 2:00pm', 'where' => 'Science Quad'],
        ];
      ?>
      <?php foreach ($events as $e): ?>
        <article class="nb-home-card rounded-3xl border border-amber-200 bg-amber-50/80 p-5 transition hover:border-amber-300 dark:border-amber-400/25 dark:bg-gradient-to-br dark:from-amber-500/10 dark:to-fuchsia-950/25 dark:hover:border-amber-300/45">
          <div class="text-xs font-semibold text-amber-800 dark:text-amber-200/90"><?= htmlspecialchars($e['when']) ?></div>
          <h3 class="mt-3 text-base font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($e['title']) ?></h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300"><?= htmlspecialchars($e['where']) ?></p>
          <div class="mt-4 text-sm font-semibold text-indigo-600 dark:text-cyan-300">Details</div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="news" class="nb-home-band border-t border-slate-200 bg-slate-50 dark:border-fuchsia-500/15 dark:bg-[#0c1028]">
  <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <div class="nb-home-accent text-sm font-semibold text-cyan-700 dark:text-cyan-300">News</div>
        <h2 class="nb-home-heading mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Latest updates</h2>
        <p class="nb-home-body mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-300">Static for now—later it becomes a real MySQL-powered feed.</p>
      </div>
      <a href="#contact" class="text-sm font-semibold text-violet-700 transition hover:text-violet-900 dark:text-fuchsia-300 dark:hover:text-fuchsia-200">Subscribe →</a>
    </div>

    <div class="mt-8 grid gap-4 lg:grid-cols-3">
      <?php
        $news = [
          ['title' => 'New labs opening this semester', 'date' => 'Apr 2026', 'desc' => 'Upgraded spaces for engineering and health sciences.'],
          ['title' => 'Internship week: meet hiring partners', 'date' => 'Mar 2026', 'desc' => 'Resume review, mock interviews, and company booths.'],
          ['title' => 'Student clubs showcase', 'date' => 'Feb 2026', 'desc' => 'Find your community and build leadership skills.'],
        ];
      ?>
      <?php foreach ($news as $n): ?>
        <article class="nb-home-card rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 dark:border-fuchsia-500/25 dark:bg-gradient-to-br dark:from-violet-950/45 dark:to-fuchsia-950/20 dark:hover:border-cyan-400/35">
          <div class="flex items-center justify-between gap-4">
            <div class="text-xs font-semibold text-violet-700 dark:text-fuchsia-300/90"><?= htmlspecialchars($n['date']) ?></div>
            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800 ring-1 ring-violet-200 dark:bg-fuchsia-500/15 dark:text-fuchsia-100 dark:ring-fuchsia-400/35">Update</span>
          </div>
          <h3 class="mt-3 text-base font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($n['title']) ?></h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300"><?= htmlspecialchars($n['desc']) ?></p>
          <div class="mt-4 text-sm font-semibold text-indigo-600 dark:text-cyan-300">Read more</div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>


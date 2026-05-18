<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScholarAI — Smarter Study with AI</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            color-scheme: light;
            color: #0f172a;
            font-family: 'Outfit', 'Inter', system-ui, sans-serif;
            background-color: #f8fafc;
        }

        body {
            min-height: 100vh;
            background-image: radial-gradient(circle at top left, rgba(59, 130, 246, 0.08), transparent 30%),
                              radial-gradient(circle at bottom right, rgba(139, 92, 246, 0.1), transparent 26%),
                              linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        .hero-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.93) 100%);
            backdrop-filter: blur(14px);
            box-shadow: 0 36px 96px rgba(15, 23, 42, 0.09);
        }

        .feature-icon {
            box-shadow: 0 22px 46px rgba(59, 130, 246, 0.14);
        }

        .floating-badge {
            transform: translateY(-8px);
            opacity: 0.98;
        }

        .hero-dot {
            box-shadow: 0 26px 90px rgba(59, 130, 246, 0.12);
        }
    </style>
</head>
<body class="antialiased text-slate-900">
    <div class="min-h-screen">
        <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-slate-900">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-violet-600 text-white shadow-lg shadow-blue-500/10">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5v11M17 8.5L12 4 7 8.5M7 15.5L12 20l5-4.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-lg leading-none">ScholarAI</p>
                        <p class="text-xs text-slate-500">AI-powered study assistant</p>
                    </div>
                </a>

                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 transition hover:text-blue-600">Login</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-full bg-gradient-to-r from-blue-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:shadow-blue-500/30">Start Free</a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 pb-16 pt-10 lg:px-8">
            <section class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div class="space-y-6 lg:max-w-xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-1 text-sm font-medium text-blue-700 shadow-sm shadow-blue-200/50">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        Premium note-driven study assistant
                    </div>
                    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Turn your lecture notes into instant study power.</h1>
                    <p class="max-w-2xl text-lg leading-8 text-slate-600">Upload your documents, get AI-guided chat help, auto-generated flashcards, and smart quizzes — all tuned to your exact course material.</p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800">Create an account</a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:border-blue-300 hover:text-blue-600">See it in action</a>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-4 shadow-sm">
                            <p class="text-sm text-slate-500">Notes processed</p>
                            <p class="mt-2 text-xl font-semibold text-slate-900">150K+</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-4 shadow-sm">
                            <p class="text-sm text-slate-500">Active learners</p>
                            <p class="mt-2 text-xl font-semibold text-slate-900">27K</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-4 shadow-sm">
                            <p class="text-sm text-slate-500">Quiz questions</p>
                            <p class="mt-2 text-xl font-semibold text-slate-900">420K</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="hero-card relative overflow-hidden rounded-[2rem] border border-slate-200/70 p-6 md:p-8">
                        <div class="absolute -left-12 top-8 h-24 w-24 rounded-full bg-blue-500/10 blur-3xl hero-dot"></div>
                        <div class="absolute -right-10 top-24 h-28 w-28 rounded-full bg-violet-500/10 blur-3xl hero-dot"></div>
                        <div class="relative z-10 grid gap-6">
                            <div class="rounded-[1.5rem] bg-gradient-to-br from-white to-slate-50 p-5 shadow-[0_22px_80px_rgba(15,23,42,0.08)]">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm uppercase tracking-[0.18em] text-slate-400">ScholarAI chat</p>
                                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Ask anything from your notes</h2>
                                    </div>
                                    <span class="rounded-2xl bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Verified</span>
                                </div>
                                <div class="mt-6 space-y-4">
                                    <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                        <p class="text-sm text-slate-500">How many chapters cover module 2?</p>
                                        <div class="mt-3 rounded-3xl bg-slate-900/95 p-4 text-sm text-white">The main concepts are introduced in chapters 5, 6 and 7 with additional examples in the appendix.</div>
                                    </div>
                                    <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                        <p class="text-sm text-slate-500">Explain the key formula from page 14.</p>
                                        <div class="mt-3 rounded-3xl bg-blue-50 p-4 text-sm text-slate-900">The formula calculates the net effect of force and provides a step-by-step solution based on your notes.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-[1.5rem] border border-slate-200/80 bg-white p-5 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Flashcards</p>
                                    <p class="mt-4 text-lg font-semibold text-slate-900">Instant knowledge cards</p>
                                </div>
                                <div class="rounded-[1.5rem] border border-slate-200/80 bg-white p-5 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Quizzes</p>
                                    <p class="mt-4 text-lg font-semibold text-slate-900">Practice with tailored questions</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-8 left-6 rounded-full bg-gradient-to-r from-blue-500 to-violet-500 px-6 py-3 text-white shadow-xl shadow-blue-500/15 floating-badge">
                        <span class="font-semibold">Study smarter with every upload</span>
                    </div>
                </div>
            </section>

            <section class="mt-20 rounded-[2rem] border border-slate-200/80 bg-white/90 p-8 shadow-[0_24px_90px_rgba(15,23,42,0.06)]">
                <div class="grid gap-10 lg:grid-cols-3">
                    <div class="space-y-4">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-3xl bg-blue-50 text-blue-600 feature-icon">
                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8M8 12h8M8 16h8" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Smart note understanding</h3>
                        <p class="text-slate-600">ScholarAI reads your documents and answers questions with context-aware precision.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-3xl bg-violet-50 text-violet-600 feature-icon">
                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m4-4H8" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Built for students</h3>
                        <p class="text-slate-600">From flashcards to quizzes, every feature is tailored for efficient revision.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-3xl bg-emerald-50 text-emerald-600 feature-icon">
                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Confidence boosting</h3>
                        <p class="text-slate-600">Track progress with generated quizzes and instant feedback on course concepts.</p>
                    </div>
                </div>
            </section>

            <section class="mt-20 grid gap-8 lg:grid-cols-[0.9fr_0.7fr] lg:gap-10">
                <div class="space-y-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-blue-600">Why students love ScholarAI</p>
                    <h2 class="text-3xl font-semibold text-slate-900">Study faster, retain more, and stay confident.</h2>
                    <p class="max-w-2xl text-base leading-8 text-slate-600">ScholarAI combines your course material with AI-powered workflows so you spend less time searching and more time learning. It keeps every response anchored to your notes, while still giving you the friendly help you need.</p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200/80 bg-slate-50 p-6">
                            <p class="text-sm font-semibold text-slate-900">Upload notes</p>
                            <p class="mt-3 text-slate-600">Drag in textbooks, slides, or PDFs and let ScholarAI process them instantly.</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200/80 bg-slate-50 p-6">
                            <p class="text-sm font-semibold text-slate-900">Interact naturally</p>
                            <p class="mt-3 text-slate-600">Ask questions like you would with a tutor and get clear, note-based answers.</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-[2rem] bg-gradient-to-br from-slate-900 to-blue-600 p-8 text-white shadow-[0_35px_120px_rgba(15,23,42,0.18)]">
                    <div class="space-y-6">
                        <div class="rounded-3xl bg-white/10 p-5">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-300">Focus</p>
                            <p class="mt-3 text-xl font-semibold">Clear explanations based on your notes.</p>
                        </div>
                        <div class="rounded-3xl bg-white/10 p-5">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-300">Retention</p>
                            <p class="mt-3 text-xl font-semibold">Memorize faster with flashcards and quiz practice.</p>
                        </div>
                        <div class="rounded-3xl bg-white/10 p-5">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-300">Confidence</p>
                            <p class="mt-3 text-xl font-semibold">Review smarter and walk into exams prepared.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200/80 bg-white/90 py-10">
            <div class="mx-auto flex max-w-7xl flex-col gap-8 px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-violet-600 text-white shadow-lg shadow-blue-500/10">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5v11M17 8.5L12 4 7 8.5M7 15.5L12 20l5-4.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">ScholarAI</p>
                            <p class="text-sm text-slate-500">Learn better with note-aware AI.</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <a href="#" class="text-sm text-slate-600 transition hover:text-slate-900">About</a>
                    <a href="#" class="text-sm text-slate-600 transition hover:text-slate-900">Privacy</a>
                    <a href="#" class="text-sm text-slate-600 transition hover:text-slate-900">Contact</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>

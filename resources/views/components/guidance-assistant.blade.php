<div
    x-data="guidanceAssistantWidget({
        bootUrl: @js(route('guidance-assistant.boot')),
        endpoint: @js(route('guidance-assistant.chat')),
        csrfToken: @js(csrf_token())
    })"
    x-init="init()"
    class="ga"
    x-cloak
>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap');
 
        /* ── Reset ── */
        .ga, .ga * { box-sizing: border-box; margin: 0; padding: 0; }
        .ga[x-cloak] { display: none !important; }
 
        /* ── Root ── */
        .ga {
            --ga-ink:      #1e293b;
            --ga-muted:    #64748b;
            --ga-border:   #e2e8f0;
            --ga-surface:  #ffffff;
            --ga-bg:       #f8fafc;
            --ga-accent:   #0d9488;
            --ga-accent-soft: #ccfbf1;
            --ga-user-bg:  #0f172a;
            --ga-user-fg:  #f1f5f9;
            --ga-radius:   1rem;
            --ga-shadow:   0 20px 50px -12px rgba(0,0,0,.18), 0 0 0 1px rgba(0,0,0,.04);
 
            position: fixed;
            right: 1rem;
            bottom: calc(env(safe-area-inset-bottom, 0px) + 1.2rem);
            z-index: 9998;
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 15px;
            color: var(--ga-ink);
            -webkit-font-smoothing: antialiased;
        }
 
        /* ── Launcher ── */
        .ga-fab {
            display: flex;
            align-items: center;
            gap: .6rem;
            height: 3.2rem;
            padding: 0 1.1rem 0 .65rem;
            border: 0;
            border-radius: 999px;
            background: var(--ga-user-bg);
            color: #fff;
            font-family: inherit;
            font-size: .82rem;
            font-weight: 600;
            letter-spacing: .01em;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(15,23,42,.28);
            transition: transform .2s cubic-bezier(.4,0,.2,1), box-shadow .2s ease;
        }
        .ga-fab:hover  { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(15,23,42,.34); }
        .ga-fab:active { transform: scale(.97); }
        .ga-fab-icon {
            width: 1.85rem; height: 1.85rem;
            border-radius: 999px;
            display: grid; place-items: center;
            background: rgba(255,255,255,.12);
            font-size: .7rem; font-weight: 700;
        }
 
        /* ── Panel ── */
        .ga-panel {
            position: absolute;
            right: 0;
            bottom: calc(100% + .6rem);
            width: min(24rem, calc(100vw - 1.2rem));
            height: min(38rem, calc(100dvh - 6rem));
            display: flex;
            flex-direction: column;
            border-radius: var(--ga-radius);
            background: var(--ga-bg);
            border: 1px solid var(--ga-border);
            box-shadow: var(--ga-shadow);
            overflow: hidden;
            transform-origin: bottom right;
        }
 
        /* ── Header ── */
        .ga-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            padding: .7rem .8rem;
            background: var(--ga-surface);
            border-bottom: 1px solid var(--ga-border);
        }
        .ga-header-left {
            display: flex;
            align-items: center;
            gap: .55rem;
            min-width: 0;
        }
        .ga-logo {
            width: 2rem; height: 2rem; flex-shrink: 0;
            border-radius: .6rem;
            display: grid; place-items: center;
            background: var(--ga-accent);
            color: #fff;
            font-size: .65rem; font-weight: 700;
        }
        .ga-header-text { min-width: 0; }
        .ga-header-text strong {
            display: block;
            font-size: .85rem;
            font-weight: 700;
            color: var(--ga-ink);
            line-height: 1.15;
        }
        .ga-header-text span {
            display: block;
            font-size: .68rem;
            color: var(--ga-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ga-header-actions { display: flex; gap: .25rem; }
        .ga-hdr-btn {
            width: 1.75rem; height: 1.75rem;
            border: 1px solid var(--ga-border);
            border-radius: .5rem;
            background: var(--ga-surface);
            color: var(--ga-muted);
            font-size: .85rem;
            cursor: pointer;
            display: grid; place-items: center;
            transition: background .15s, color .15s;
        }
        .ga-hdr-btn:hover { background: var(--ga-bg); color: var(--ga-ink); }
 
        /* ── Thread ── */
        .ga-thread {
            flex: 1 1 0;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: .85rem .75rem;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            scroll-behavior: smooth;
        }
        .ga-thread::-webkit-scrollbar { width: 4px; }
        .ga-thread::-webkit-scrollbar-thumb { background: var(--ga-border); border-radius: 4px; }
 
        /* ── Message rows ── */
        .ga-row { display: flex; max-width: 100%; }
        .ga-row--user { justify-content: flex-end; }
        .ga-row--bot  { justify-content: flex-start; }
 
        /* ── Bubbles ── */
        .ga-bubble { max-width: 88%; }
 
        .ga-row--user .ga-bubble {
            padding: .6rem .85rem;
            background: var(--ga-user-bg);
            color: var(--ga-user-fg);
            border-radius: var(--ga-radius) var(--ga-radius) .3rem var(--ga-radius);
            font-size: .84rem;
            line-height: 1.5;
            word-break: break-word;
        }
 
        .ga-row--bot .ga-bubble {
            padding: .55rem 0;
            font-size: .84rem;
            line-height: 1.55;
            color: var(--ga-ink);
            word-break: break-word;
        }
        .ga-bot-label {
            display: inline-block;
            margin-bottom: .15rem;
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--ga-accent);
        }
        .ga-bot-text { display: block; white-space: pre-wrap; }
 
        /* ── Actions & suggestions ── */
        .ga-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .5rem;
        }
        .ga-chip {
            display: inline-flex;
            align-items: center;
            height: 1.75rem;
            padding: 0 .7rem;
            border: 1px solid var(--ga-border);
            border-radius: 999px;
            background: var(--ga-surface);
            color: var(--ga-ink);
            font-family: inherit;
            font-size: .72rem;
            font-weight: 600;
            white-space: nowrap;
            text-decoration: none;
            cursor: pointer;
            transition: border-color .15s, background .15s, color .15s;
        }
        .ga-chip:hover {
            border-color: var(--ga-accent);
            color: var(--ga-accent);
            background: var(--ga-accent-soft);
        }
        .ga-chip--accent {
            border-color: var(--ga-accent);
            color: var(--ga-accent);
        }
        .ga-chip--primary {
            background: var(--ga-accent);
            border-color: transparent;
            color: #ffffff;
        }
        .ga-chip--primary:hover {
            background: #0f766e;
            border-color: transparent;
            color: #ffffff;
        }
        .ga-chip--secondary {
            background: var(--ga-accent-soft);
            border-color: rgba(13,148,136,.18);
            color: var(--ga-accent);
        }
        .ga-chip--ghost {
            background: var(--ga-surface);
            border-color: var(--ga-border);
            color: var(--ga-ink);
        }
 
        /* ── Typing indicator ── */
        .ga-typing {
            display: inline-flex;
            align-items: center;
            gap: .22rem;
            padding: .5rem 0;
        }
        .ga-typing-dot {
            width: 5px; height: 5px;
            border-radius: 999px;
            background: var(--ga-accent);
            opacity: .4;
            animation: ga-pulse .9s ease-in-out infinite;
        }
        .ga-typing-dot:nth-child(2) { animation-delay: .15s; }
        .ga-typing-dot:nth-child(3) { animation-delay: .3s; }
        @keyframes ga-pulse {
            0%, 80%, 100% { opacity: .3; transform: scale(.8); }
            40%            { opacity: 1;  transform: scale(1); }
        }
 
        /* ── Compose ── */
        .ga-compose {
            padding: .55rem .65rem .7rem;
            background: var(--ga-surface);
            border-top: 1px solid var(--ga-border);
        }
        .ga-compose-inner {
            display: flex;
            align-items: flex-end;
            gap: .4rem;
            min-height: 2.75rem;
            padding: .25rem .25rem .25rem .85rem;
            border: 1.5px solid var(--ga-border);
            border-radius: var(--ga-radius);
            background: var(--ga-surface);
            transition: border-color .2s;
        }
        .ga-compose-inner:focus-within {
            border-color: var(--ga-accent);
            box-shadow: 0 0 0 3px rgba(13,148,136,.1);
        }
        .ga-textarea {
            flex: 1;
            min-height: 1.5rem;
            max-height: 5.5rem;
            border: 0;
            outline: 0;
            resize: none;
            background: transparent;
            color: var(--ga-ink);
            font-family: inherit;
            font-size: .84rem;
            line-height: 1.4;
        }
        .ga-textarea::placeholder { color: var(--ga-muted); }
        .ga-send {
            flex-shrink: 0;
            width: 2.15rem; height: 2.15rem;
            border: 0;
            border-radius: .65rem;
            background: var(--ga-accent);
            color: #fff;
            font-size: .8rem;
            cursor: pointer;
            display: grid; place-items: center;
            transition: opacity .15s, transform .1s;
        }
        .ga-send:disabled { opacity: .35; cursor: default; }
        .ga-send:not(:disabled):hover { transform: scale(1.06); }
        .ga-send svg { width: 1rem; height: 1rem; }
 
        .ga-hint {
            margin-top: .3rem;
            font-size: .62rem;
            color: var(--ga-muted);
            text-align: center;
        }
 
        /* ── Responsive ── */
        @media (max-width: 640px) {
            .ga {
                right: .6rem;
                bottom: calc(env(safe-area-inset-bottom, 0px) + .6rem);
            }
            .ga-panel {
                width: calc(100vw - 1.2rem);
                height: min(34rem, calc(100dvh - 4.5rem));
            }
            .ga-fab span:last-child { display: none; }
        }
 
        /* ── Entrance animation ── */
        @keyframes ga-slide-up {
            from { opacity: 0; transform: translateY(12px) scale(.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .ga-panel-enter { animation: ga-slide-up .25s cubic-bezier(.22,1,.36,1) both; }
    </style>
 
    <!-- ── FAB launcher ── -->
    <button type="button" class="ga-fab" @click="toggleOpen()" aria-label="Ouvrir l'assistant">
        <span class="ga-fab-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </span>
        <span>Assistant</span>
    </button>
 
    <!-- ── Chat panel ── -->
    <section
        x-show="open"
        x-transition:enter="ga-panel-enter"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="ga-panel"
        @click.outside="open = false; persistOpenState()"
        @keydown.escape.window="open = false; persistOpenState()"
    >
        <!-- Header -->
        <header class="ga-header">
            <div class="ga-header-left">
                <span class="ga-logo">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
                <div class="ga-header-text">
                    <strong>Assistant</strong>
                    <span>Posez votre question</span>
                </div>
            </div>
            <div class="ga-header-actions">
                <button type="button" class="ga-hdr-btn" @click="resetConversation()" x-show="booted" title="Recommencer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                </button>
                <button type="button" class="ga-hdr-btn" @click="open = false; persistOpenState()" title="Fermer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </header>
 
        <!-- Thread -->
        <div class="ga-thread" x-ref="thread" aria-live="polite">
            <template x-for="item in messages" :key="item.id">
                <div :class="item.role === 'user' ? 'ga-row ga-row--user' : 'ga-row ga-row--bot'">
                    <!-- User bubble -->
                    <template x-if="item.role === 'user'">
                        <div class="ga-bubble" x-text="item.text"></div>
                    </template>
 
                    <!-- Bot bubble -->
                    <template x-if="item.role === 'assistant'">
                        <div class="ga-bubble">
                            <span class="ga-bot-label">Assistant</span>
                            <span class="ga-bot-text" x-text="item.text"></span>
 
                            <!-- Action links -->
                            <template x-if="item.isInteractive && item.actions && item.actions.length">
                                <div class="ga-chips">
                                    <template x-for="action in item.actions" :key="action.id">
                                        <a :href="action.url" :class="'ga-chip ' + actionClass(action.style)" x-text="action.label"></a>
                                    </template>
                                </div>
                            </template>
 
                            <!-- Suggestions -->
                            <template x-if="item.isInteractive && item.suggestions && item.suggestions.length">
                                <div class="ga-chips">
                                    <template x-for="suggestion in item.suggestions" :key="suggestion">
                                        <button type="button" class="ga-chip" @click="send(suggestion)" x-text="suggestion"></button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
 
            <!-- Typing indicator -->
            <template x-if="loading">
                <div class="ga-row ga-row--bot">
                    <div class="ga-bubble">
                        <span class="ga-bot-label">Assistant</span>
                        <div class="ga-typing" aria-label="Chargement">
                            <span class="ga-typing-dot"></span>
                            <span class="ga-typing-dot"></span>
                            <span class="ga-typing-dot"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
 
        <!-- Compose -->
        <div class="ga-compose">
            <form class="ga-compose-inner" @submit.prevent="send()">
                <textarea
                    x-model="composer"
                    class="ga-textarea"
                    rows="1"
                    maxlength="500"
                    placeholder="Votre question…"
                    @input="autosize($event.target)"
                    @keydown.enter.exact.prevent="send()"
                ></textarea>
                <button type="submit" class="ga-send" :disabled="loading || !composer.trim()" aria-label="Envoyer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
            <p class="ga-hint">Entrée pour envoyer · Shift+Entrée pour un retour à la ligne</p>
        </div>
    </section>
 
    <script>
        function guidanceAssistantWidget(config) {
            return {
                open: false,
                loading: false,
                booted: false,
                composer: '',
                previousResponseId: null,
                messages: [],
 
                init() {
                    try {
                        this.open = window.sessionStorage.getItem('tp-chatbot-open') === '1';
                    } catch (_) {
                        this.open = false;
                    }
                    if (this.open) this.bootstrap();
                },
 
                persistOpenState() {
                    try { window.sessionStorage.setItem('tp-chatbot-open', this.open ? '1' : '0'); } catch (_) {}
                },
 
                async bootstrap(force = false) {
                    if (this.booted && !force) return;
                    this.loading = true;
 
                    try {
                        const res = await fetch(config.bootUrl, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) throw new Error('boot_failed');
                        const data = await res.json();
 
                        this.messages = [this.makeBot({
                            text: data.welcome_message || 'Bonjour ! Comment puis-je vous aider ?',
                            actions: Array.isArray(data.starter_actions) ? data.starter_actions : [],
                            suggestions: Array.isArray(data.starter_questions) ? data.starter_questions : [],
                        })];
                    } catch (_) {
                        this.messages = [this.makeBot({
                            text: 'Bonjour ! Je peux vous aider pour vos commandes, paiements et messages.',
                            actions: [],
                            suggestions: ['Où voir ma commande ?', 'Où voir mes paiements ?'],
                        })];
                    } finally {
                        this.loading = false;
                        this.booted = true;
                        this.scrollThread();
                    }
                },
 
                async toggleOpen() {
                    this.open = !this.open;
                    this.persistOpenState();
                    if (!this.open) return;
                    if (!this.booted) await this.bootstrap();
                    this.$nextTick(() => { this.focusComposer(); this.scrollThread(); });
                },
 
                async resetConversation() {
                    this.previousResponseId = null;
                    this.messages = [];
                    this.booted = false;
                    await this.bootstrap(true);
                    this.$nextTick(() => this.focusComposer());
                },
 
                focusComposer() {
                    const el = this.$el.querySelector('.ga-textarea');
                    if (el) { el.focus(); this.autosize(el); }
                },
 
                autosize(el) {
                    if (!el) el = this.$el.querySelector('.ga-textarea');
                    if (!el) return;
                    el.style.height = 'auto';
                    el.style.height = Math.min(el.scrollHeight, 88) + 'px';
                },
 
                deactivateChoices() {
                    this.messages = this.messages.map(m => m.role === 'assistant' ? { ...m, isInteractive: false } : m);
                },
 
                makeBot(payload) {
                    return {
                        id: this.uid(),
                        role: 'assistant',
                        text: payload.text || 'Je n\'ai pas pu formuler une réponse.',
                        actions: Array.isArray(payload.actions) ? payload.actions : [],
                        suggestions: Array.isArray(payload.suggestions) ? payload.suggestions : [],
                        isInteractive: true,
                    };
                },
 
                async send(prefill = null) {
                    const text = String(prefill ?? this.composer).trim();
                    if (!text || this.loading) return;
                    if (!this.booted) await this.bootstrap();
 
                    this.deactivateChoices();
                    this.messages.push({
                        id: this.uid(), role: 'user', text,
                        actions: [], suggestions: [], isInteractive: false,
                    });
 
                    this.composer = '';
                    this.loading = true;
                    this.autosize();
                    this.scrollThread();
 
                    try {
                        const res = await fetch(config.endpoint, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                message: text,
                                previous_response_id: this.previousResponseId,
                                current_path: window.location.pathname,
                            }),
                        });
                        if (!res.ok) throw new Error('chat_failed');
                        const data = await res.json();
 
                        this.previousResponseId = data.previous_response_id || null;
                        this.messages.push(this.makeBot({
                            text: data.message,
                            actions: data.actions,
                            suggestions: data.suggestions,
                        }));
                    } catch (_) {
                        this.messages.push(this.makeBot({
                            text: 'Le chat est indisponible pour le moment.',
                            actions: [],
                            suggestions: ['Où voir ma commande ?', 'Où voir mes paiements ?'],
                        }));
                    } finally {
                        this.loading = false;
                        this.scrollThread();
                    }
                },
 
                scrollThread() {
                    this.$nextTick(() => {
                        const el = this.$refs.thread;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },

                actionClass(style) {
                    if (style === 'primary') return 'ga-chip--primary';
                    if (style === 'secondary') return 'ga-chip--secondary';
                    return 'ga-chip--ghost';
                },
 
                uid() {
                    return Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
                },
            };
        }
    </script>
</div>

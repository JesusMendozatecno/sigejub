
<style>
    #loading-overlay {
        /* Color de acento elegido por el usuario en su personalización.
           Se usa --user-accent (no --accent) para que los temas claro/oscuro no lo pisen.
           En páginas sin sesión (login, registro...) cae al azul institucional por defecto. */
        --sigejub-accent: var(--user-accent, #087cf0);
        --sigejub-accent-2: color-mix(in srgb, var(--sigejub-accent) 60%, #ffffff 40%);
        --sigejub-accent-3: color-mix(in srgb, var(--sigejub-accent) 38%, #ffffff 62%);
        --sigejub-accent-deep: color-mix(in srgb, var(--sigejub-accent) 72%, #04122b 28%);
        --sigejub-navy: color-mix(in srgb, var(--sigejub-accent) 50%, #07203f 50%);
        --sigejub-muted: color-mix(in srgb, var(--sigejub-accent) 26%, #5e789f 74%);
        display: none;
        position: fixed;
        inset: 0;
        width: 100%;
        min-height: 100vh;
        min-height: 100dvh;
        z-index: 100000;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        isolation: isolate;
        padding: clamp(24px, 5vw, 72px);
        box-sizing: border-box;
        color: var(--sigejub-navy);
        font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        background:
            radial-gradient(ellipse at 78% 78%, color-mix(in srgb, var(--sigejub-accent) 20%, transparent) 0%, transparent 34%),
            radial-gradient(ellipse at 13% 42%, color-mix(in srgb, var(--sigejub-accent) 16%, transparent) 0%, transparent 35%),
            linear-gradient(118deg, color-mix(in srgb, var(--sigejub-accent) 7%, #ffffff) 0%, #ffffff 48%, color-mix(in srgb, var(--sigejub-accent) 9%, #ffffff) 100%);
    }

    #loading-overlay.active { display: flex; }
    #loading-overlay *, #loading-overlay *::before, #loading-overlay *::after { box-sizing: border-box; }

    #loading-overlay .loading-decoration {
        position: absolute;
        z-index: -1;
        pointer-events: none;
        user-select: none;
    }

    #loading-overlay .loading-decoration--top {
        top: -150px;
        right: -75px;
        width: min(36vw, 540px);
        height: 310px;
        border-radius: 0 0 0 68%;
        transform: rotate(7deg);
        background: linear-gradient(135deg, color-mix(in srgb, var(--sigejub-accent) 24%, transparent), color-mix(in srgb, var(--sigejub-accent) 90%, #001033 10%));
        clip-path: polygon(30% 0, 100% 0, 100% 100%, 73% 91%, 54% 61%);
    }

    #loading-overlay .loading-decoration--bottom {
        left: -8%;
        bottom: -245px;
        width: 72%;
        height: 430px;
        border-radius: 50% 55% 0 0;
        transform: rotate(19deg);
        background: linear-gradient(130deg, var(--sigejub-accent-deep) 8%, var(--sigejub-accent) 62%, var(--sigejub-accent-2) 100%);
        box-shadow: 0 -18px 0 color-mix(in srgb, var(--sigejub-accent) 20%, transparent), 0 -38px 0 color-mix(in srgb, var(--sigejub-accent) 12%, transparent);
    }

    #loading-overlay .loading-decoration--bottom::before {
        content: "";
        position: absolute;
        top: 18px;
        left: 0;
        width: 100%;
        height: 3px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .75);
        transform: rotate(2deg);
    }

    #loading-overlay .loading-architecture {
        position: absolute;
        right: -1.5%;
        top: 17%;
        width: min(32vw, 510px);
        height: auto;
        opacity: .075;
        color: var(--sigejub-accent);
        pointer-events: none;
    }

    #loading-overlay .loading-brand {
        position: absolute;
        top: clamp(24px, 5.5vh, 58px);
        left: clamp(24px, 5.5vw, 92px);
        display: flex;
        align-items: center;
        gap: 22px;
        max-width: calc(100% - 48px);
    }

    #loading-overlay .loading-brand-mark {
        display: grid;
        flex: 0 0 auto;
        place-items: center;
        width: clamp(58px, 6.2vw, 104px);
        aspect-ratio: 1;
        border-radius: 22%;
        color: #fff;
        background: linear-gradient(145deg, var(--sigejub-accent-2) 0%, var(--sigejub-accent) 48%, var(--sigejub-accent-deep) 100%);
        box-shadow: 0 9px 22px color-mix(in srgb, var(--sigejub-accent) 20%, transparent), inset 0 1px 0 rgba(255,255,255,.35);
    }

    #loading-overlay .loading-brand-mark svg { width: 62%; height: 62%; display: block; }
    #loading-overlay .loading-brand-mark .mark-dot { fill: var(--sigejub-accent-deep); }
    #loading-overlay .loading-brand-name {
        margin: 0;
        color: var(--sigejub-navy);
        font-size: clamp(27px, 3.9vw, 60px);
        line-height: 1.02;
        letter-spacing: -.035em;
        font-weight: 850;
    }

    #loading-overlay .loading-brand-caption {
        margin: 7px 0 0;
        color: var(--sigejub-muted);
        font-size: clamp(12px, 1.35vw, 22px);
        font-weight: 500;
        line-height: 1.35;
    }

    #loading-overlay .loading-content {
        width: min(100%, 760px);
        margin: clamp(85px, 11vh, 125px) auto 0;
        text-align: center;
        animation: sigejubLoadingEnter .55s ease both;
    }

    #loading-overlay .loading-spinner { display: flex; justify-content: center; margin: 0 auto 24px; }
    #loading-overlay .spinner-circle {
        width: clamp(88px, 11vw, 180px);
        height: clamp(88px, 11vw, 180px);
        border: clamp(8px, .95vw, 15px) solid color-mix(in srgb, var(--sigejub-accent) 20%, transparent);
        border-top-color: var(--sigejub-accent-deep);
        border-right-color: var(--sigejub-accent);
        border-bottom-color: var(--sigejub-accent-2);
        border-left-color: var(--sigejub-accent-3);
        border-radius: 50%;
        animation: sigejubLoadingSpin 1.15s cubic-bezier(.55,.1,.45,.9) infinite;
        filter: drop-shadow(0 8px 13px color-mix(in srgb, var(--sigejub-accent) 8%, transparent));
    }

    #loading-overlay .loading-title {
        margin: 0;
        color: var(--sigejub-navy);
        font-size: clamp(27px, 3.1vw, 49px);
        font-weight: 800;
        line-height: 1.18;
        letter-spacing: -.035em;
    }

    #loading-overlay .loading-title span {
        color: var(--sigejub-accent);
        background: linear-gradient(90deg, var(--sigejub-accent), var(--sigejub-accent-2));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    #loading-overlay .loading-text {
        margin: 17px 0 0;
        color: var(--sigejub-muted);
        font-size: clamp(15px, 1.55vw, 25px);
        font-weight: 500;
        line-height: 1.5;
    }

    #loading-overlay .loading-progress {
        position: relative;
        width: min(100%, 740px);
        height: clamp(13px, 2.1vw, 34px);
        margin: clamp(26px, 4.2vh, 43px) auto 0;
        overflow: hidden;
        border-radius: 999px;
        background: color-mix(in srgb, var(--sigejub-accent) 18%, transparent);
        box-shadow: inset 0 1px 2px color-mix(in srgb, var(--sigejub-accent) 6%, transparent);
    }

    #loading-overlay .loading-progress-bar {
        position: absolute;
        inset: 0 auto 0 0;
        width: 64%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--sigejub-accent-deep) 0%, var(--sigejub-accent) 68%, var(--sigejub-accent-2) 100%);
        box-shadow: 0 2px 10px color-mix(in srgb, var(--sigejub-accent) 14%, transparent);
        animation: sigejubLoadingProgress 2.2s ease-in-out infinite;
    }

    #loading-overlay .loading-security {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin: clamp(27px, 5.5vh, 55px) auto 0;
        color: var(--sigejub-muted);
        font-size: clamp(13px, 1.35vw, 21px);
        font-weight: 500;
        line-height: 1.4;
    }

    #loading-overlay .loading-security-icon { display: inline-flex; flex: 0 0 auto; width: 34px; height: 39px; color: var(--sigejub-accent); }
    #loading-overlay .loading-security-divider { height: 34px; width: 2px; background: color-mix(in srgb, var(--sigejub-accent) 25%, transparent); }

    @keyframes sigejubLoadingSpin { to { transform: rotate(360deg); } }
    @keyframes sigejubLoadingEnter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes sigejubLoadingProgress { 0% { width: 38%; transform: translateX(-8%); } 55% { width: 67%; transform: translateX(25%); } 100% { width: 42%; transform: translateX(145%); } }

    @media (max-width: 640px) {
        #loading-overlay { padding: 24px 22px 38px; }
        #loading-overlay .loading-brand { top: 25px; left: 22px; gap: 12px; }
        #loading-overlay .loading-brand-mark { width: 54px; border-radius: 13px; }
        #loading-overlay .loading-brand-name { font-size: 29px; }
        #loading-overlay .loading-brand-caption { max-width: 230px; font-size: 11px; margin-top: 4px; }
        #loading-overlay .loading-content { margin-top: 55px; }
        #loading-overlay .loading-spinner { margin-bottom: 25px; }
        #loading-overlay .spinner-circle { width: 88px; height: 88px; border-width: 8px; }
        #loading-overlay .loading-title { font-size: clamp(27px, 7.2vw, 38px); }
        #loading-overlay .loading-text { margin-top: 12px; font-size: 15px; }
        #loading-overlay .loading-progress { margin-top: 29px; height: 14px; }
        #loading-overlay .loading-security { gap: 10px; margin-top: 32px; font-size: 12px; }
        #loading-overlay .loading-security-icon { width: 27px; height: 31px; }
        #loading-overlay .loading-security-divider { height: 28px; }
        #loading-overlay .loading-architecture { width: 70vw; top: 30%; right: -22%; }
        #loading-overlay .loading-decoration--bottom { width: 110%; bottom: -300px; left: -25%; }
        #loading-overlay .loading-decoration--top { width: 70vw; right: -35%; top: -195px; }
    }

    @media (prefers-reduced-motion: reduce) {
        #loading-overlay .spinner-circle,
        #loading-overlay .loading-progress-bar,
        #loading-overlay .loading-content { animation-duration: .01ms !important; animation-iteration-count: 1 !important; }
    }
</style>

<div id="loading-overlay" role="status" aria-live="polite" aria-label="Cargando SIGEJUB">
    <div class="loading-decoration loading-decoration--top" aria-hidden="true"></div>
    <div class="loading-decoration loading-decoration--bottom" aria-hidden="true"></div>

    
    <svg class="loading-architecture" viewBox="0 0 520 470" fill="currentColor" aria-hidden="true">
        <path d="M35 135 260 20l225 115v22H35z"/>
        <path d="M65 173h390v18H65zM80 405h360v22H80zM48 440h424v18H48z"/>
        <path d="M105 198h48v190h-48zM190 198h48v190h-48zM275 198h48v190h-48zM360 198h48v190h-48z"/>
        <path d="M90 185h78v20H90zM175 185h78v20h-78zM260 185h78v20h-78zM345 185h78v20h-78z"/>
    </svg>

    <header class="loading-brand" aria-label="SIGEJUB, Sistema Integral de Gestión de Jubilaciones">
        <span class="loading-brand-mark" aria-hidden="true">
            <svg viewBox="0 0 64 64" fill="none">
                <path d="M8 23 32 10l24 13v4H8z" fill="currentColor"/>
                <path d="M13 31h6v18h-6zM29 31h6v18h-6zM45 31h6v18h-6z" fill="currentColor"/>
                <path d="M8 51h48v5H8zM6 59h52v4H6z" fill="currentColor"/>
                <path class="mark-dot" d="M31 17h2v3h-2z"/>
            </svg>
        </span>
        <span>
            <p class="loading-brand-name">SIGEJUB</p>
            <p class="loading-brand-caption">Sistema Integral de Gestión de Jubilaciones</p>
        </span>
    </header>

    <main class="loading-content">
        <div class="loading-spinner" aria-hidden="true">
            <div class="spinner-circle"></div>
        </div>

        <h1 class="loading-title">Cargando <span>el sistema...</span></h1>
        <p class="loading-text" id="loadingText">Por favor, espere un momento.</p>

        <div class="loading-progress" aria-hidden="true">
            <div class="loading-progress-bar"></div>
        </div>

        <div class="loading-security">
            <span class="loading-security-icon" aria-hidden="true">
                <svg viewBox="0 0 36 42" width="100%" height="100%" fill="none">
                    <path d="M18 2 33 8v11c0 9-6.3 15.5-15 20C9.3 34.5 3 28 3 19V8z" fill="currentColor"/>
                    <path d="m11 20 4.5 4.5L25.5 14" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="loading-security-divider" aria-hidden="true"></span>
            <span>Tu información, nuestra prioridad</span>
        </div>
    </main>
</div>
<?php /**PATH C:\xampp\htdocs\Nueva carpeta (2)\sigejub 2\original\resources\views/partials/loading-overlay.blade.php ENDPATH**/ ?>
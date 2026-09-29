<style>
    .redil-central { --redil-ink: #101b19; --redil-mint: #b8f3e7; --redil-turquoise: #00d4b4; --bs-primary: #007a67; --bs-primary-rgb: 0,122,103; background: #f6faf9 !important; color: #253d37; }
    .redil-central h1, .redil-central h2, .redil-central h3, .redil-central h4, .redil-central h5, .redil-central h6 { color: var(--redil-ink); }
    .redil-topbar { display:flex; align-items:center; gap:28px; padding:22px 4%; background:#fff; border-bottom:1px solid #dce9e5; flex-wrap:wrap; }
    .redil-brand { display:flex; gap:12px; align-items:center; color:#101b19 !important; text-decoration:none; }
    .redil-brand strong { font-size:26px; letter-spacing:-1px; font-weight:800; }
    .redil-brand small { display:block; font-size:9px; letter-spacing:2px; color:#596c66; }
    .redil-brand-mark { display:grid; place-content:center; background:var(--redil-mint); width:48px; height:48px; border-radius:16px; font-size:32px; font-weight:800; position:relative; }
    .redil-brand-mark span { position:absolute; right:5px; top:-7px; color:#007a67; }
    .redil-nav { display:flex; flex:1; justify-content:center; gap:6px; flex-wrap:wrap; }
    .redil-nav a { color:#4e635d; padding:10px 15px; border-radius:24px; font-size:14px; font-weight:600; }
    .redil-nav a:hover, .redil-nav a[aria-current] { color:#053b30; background:#d4f7ec; }
    .redil-central main > .container-xxl { padding-top:32px !important; padding-bottom:32px !important; }
    .redil-hero { position:relative; overflow:hidden; background:var(--redil-mint); border-radius:28px; padding:38px; isolation:isolate; }
    .redil-hero::after { content:""; position:absolute; z-index:-1; right:-20px; top:0; width:40%; height:100%; background-image:radial-gradient(#00bfa2 2px, transparent 3px); background-size:20px 20px; opacity:.38; mask-image:linear-gradient(to right, transparent, black); }
    .redil-eyebrow { font-size:11px; font-weight:700; letter-spacing:2px; color:#315c50; }
    .redil-hero h1 { font-size:clamp(28px, 3.5vw, 44px); letter-spacing:-1.5px; font-weight:800; margin:16px 0 12px; max-width:800px; }
    .redil-hero p { color:#315c50; max-width:620px; margin-bottom:24px; }
    .redil-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
    .redil-stats > div { background:#fff; border:1px solid #dce9e5; border-radius:20px; padding:22px 26px; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .redil-stats span { color:#52665f; font-size:14px; }
    .redil-stats strong { color:#101b19; font-size:32px; font-weight:700; }
    .redil-central .card, .redil-central .modal-content { border:1px solid #dce9e5; border-radius:22px; box-shadow:0 4px 18px #10392a05; background:#fff; }
    .redil-central .card-header { background:transparent; border-color:#dce9e5 !important; }
    .redil-central .card-body { padding:26px; }
    .redil-central .btn { border-radius:999px; font-weight:600; transition:background-color .15s, box-shadow .15s; }
    .redil-central .btn-primary { background:#00d4b4 !important; border-color:#00d4b4 !important; color:#072c25 !important; box-shadow:none !important; }
    .redil-central .btn-primary:hover { background:#00bda0 !important; border-color:#00bda0 !important; }
    .redil-central .btn-dark { background:#101b19; color:#fff; }
    .redil-central .btn-outline-primary { color:#006e5c !important; border-color:#7acbbb !important; background:transparent; }
    .redil-central .btn-outline-primary:hover { background:#d4f7ec !important; }
    .redil-central .btn-label-primary, .redil-central .bg-label-primary { background:#dcf9f0 !important; color:#006650 !important; }
    .redil-central .btn-label-secondary, .redil-central .bg-label-secondary { background:#edf3f0 !important; color:#52645b !important; }
    .redil-central .bg-label-success { background:#d4f7ec !important; color:#076a46 !important; }
    .redil-central .text-primary, .redil-central a:not(.btn):not(.redil-brand):not(.redil-nav a) { color:#007966 !important; }
    .redil-central .text-muted { color:#63756e !important; }
    .redil-central .form-control, .redil-central .form-select, .redil-central .input-group-text { border-color:#cddcd6; border-radius:12px; background-color:#fff; color:#233e34; min-height:44px; }
    .redil-central .form-control:focus, .redil-central .form-select:focus { border-color:#007966; box-shadow:0 0 0 3px #00b99a26; }
    .redil-central .form-check-input:checked { background-color:#007966; border-color:#007966; }
    .redil-central .table { color:#30493f; --bs-table-bg:transparent; }
    .redil-central .table th { color:#425c50; }
    .redil-central .table thead { background:#f0f8f4; }
    .redil-central .table td, .redil-central .table th { border-bottom:1px dashed #dce9e5; padding:18px 22px; vertical-align:middle; }
    .redil-plan-table { min-width:830px; margin:0; }
    .redil-central .badge { border-radius:30px; padding:7px 11px; }
    .redil-central .alert { border-radius:14px; }
    .redil-central :focus-visible { outline:3px solid #006d5b; outline-offset:3px; }
    .redil-footer { text-align:center; color:#677b72; padding:24px; font-size:12px; }
    .redil-footer span { margin:0 8px; color:#007966; }
    .redil-skip { position:absolute; left:16px; top:-80px; z-index:2000; background:#fff; padding:12px; }
    .redil-skip:focus { top:8px; }
    @media(max-width:767px) {
        .redil-topbar { padding:18px; gap:16px; }
        .redil-nav { order:3; flex-basis:100%; justify-content:flex-start; }
        .redil-nav a { padding:8px 12px; }
        .redil-topbar form { margin-left:auto; }
        .redil-hero { padding:26px; border-radius:20px; }
        .redil-stats { grid-template-columns:1fr; gap:10px; }
        .redil-stats > div { padding:16px 22px; }
        .redil-central .card-body { padding:20px; }
    }
    @media(prefers-reduced-motion:reduce) { .redil-central * { scroll-behavior:auto !important; transition:none !important; } }
</style>

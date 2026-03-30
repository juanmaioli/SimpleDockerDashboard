<?php require_once 'auth.php'; checkAuth(); ?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Docker Dashboard</title>
    <!-- Favicon bi-box-seam text-info (SVG Dinámico) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%230dcaf0'><path d='M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 6.404l6.154-2.462zm3.25 3.63-6.5 2.6v7.922l6.5-2.6V6.17zm-7.5 10.522V8.77L.5 6.17v7.922zM7.5.582a1 1 0 0 1 .736 0l7.67 3.068A.5.5 0 0 1 16 4.115v8.93a1 1 0 0 1-.607.922l-7 2.8a1 1 0 0 1-.786 0l-7-2.8A1 1 0 0 1 0 13.045V4.115a.5.5 0 0 1 .308-.465z'/></svg>">
    <!-- Bootstrap 5.3 + Iconos -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-color-extension.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f172a; color: #e2e8f0; }
        .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .table { --bs-table-bg: transparent; --bs-table-color: #e2e8f0; --bs-table-border-color: #334155; }
        .table th { cursor: pointer; user-select: none; transition: background-color 0.2s; }
        .table th:hover { background-color: #334155; }
        .nav-pills .nav-link { color: #94a3b8; border-radius: 0.5rem; margin-right: 10px; font-weight: 500; }
        .nav-pills .nav-link.active { background-color: #0ea5e9; color: white; }
        .badge { border-radius: 0.5rem; font-weight: 600; }
        .stats-header { border-bottom: 1px solid #334155; margin-bottom: 1.5rem; padding-bottom: 1rem; }
        .refresh-indicator { width: 10px; height: 10px; border-radius: 50%; display: inline-block; background-color: #10b981; margin-right: 8px; animation: pulse 1s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
        .sort-icon { margin-left: 5px; opacity: 0.3; }
        .sort-active { opacity: 1; color: #0ea5e9; }
        .status-up { color: #10b981; }
        .status-down { color: #ef4444; }
        .btn-action { padding: 0.35rem 0.6rem; font-size: 0.9rem; border-radius: 0.5rem; margin-right: 4px; }

        /* Ajuste global del tamaño de letra de las tablas */
        .table td, .table th { font-size: 1.08rem; vertical-align: middle; }
        .table .text-tiny, .table .small, .table code { font-size: 0.9rem !important; }

        #logs-content, #inspect-content { background-color: #000; color: #d1d5db; font-family: 'Courier New', Courier, monospace; padding: 15px; border-radius: 8px; max-height: 500px; overflow-y: auto; white-space: pre-wrap; font-size: 0.85rem; }
        #inspect-content { color: #8be9fd; }
        .modal-content { background-color: #1e293b; border: 1px solid #334155; }
        .text-tiny { font-size: 0.75rem; }
        .badge-container { display: flex; flex-wrap: wrap; gap: 4px; }

        /* Terminal Styles - Dracula Theme */
        .terminal-window {
            background-color: #282a36;
            color: #f8f8f2;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            padding: 15px;
            border-radius: 8px;
            height: 500px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            border: 1px solid #44475a;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .terminal-output { flex-grow: 1; white-space: pre-wrap; font-size: 0.95rem; line-height: 1.4; }
        .terminal-input-line { display: flex; align-items: center; border-top: 1px solid #44475a; padding-top: 10px; margin-top: 10px; }
        .prompt { color: #bd93f9; margin-right: 8px; font-weight: bold; }
        .cmd-input { background: transparent; border: none; color: #50fa7b; flex-grow: 1; outline: none; font-family: inherit; font-size: 0.95rem; }
        .terminal-msg-success { color: #50fa7b; }
        .terminal-msg-info { color: #8be9fd; }
        .terminal-msg-warning { color: #ffb86c; }
        .terminal-msg-danger { color: #ff5555; }

        /* Alert Styles */
        .row-warning { background-color: rgba(251, 191, 36, 0.1) !important; }
        .row-danger { background-color: rgba(239, 68, 68, 0.1) !important; }
        .text-warning-custom { color: #fbbf24 !important; }
        .text-danger-custom { color: #ef4444 !important; }

        /* Estilos para filas de contenedores */
        #list-body td { vertical-align: middle; }

        /* Summary Card Styles */
        .summary-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        .summary-item { display: flex; flex-direction: column; }
        .summary-label { color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .summary-value { font-size: 1.25rem; font-weight: 700; color: #f8f8f2; }
        .summary-sub { font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; }
        .summary-icon { font-size: 3rem; margin-bottom: 0.5rem; color: #0ea5e9; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="stats-header d-flex justify-content-between align-items-center">
            <h1><i class="bi bi-box-seam text-info me-2"></i> Simple Docker Dashboard</h1>
            <div class="d-flex align-items-center">
                <div class="me-4 text-end">
                    <span class="refresh-indicator"></span>
                    <small class="text-secondary d-block">Actualizando...</small>
                </div>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>

        <!-- Resumen del Sistema -->
        <div class="summary-card" id="system-summary">
            <div class="summary-item">
                <i class="bi bi-cpu summary-icon"></i>
                <span class="summary-label">CPU & OS</span>
                <span class="summary-value" id="sum-cpu-usage">-- %</span>
                <span class="summary-sub" id="sum-os-info">Cargando...</span>
            </div>
            <div class="summary-item">
                <i class="bi bi-memory summary-icon text-warning"></i>
                <span class="summary-label">Memoria RAM</span>
                <span class="summary-value" id="sum-mem-usage">-- GB</span>
                <span class="summary-sub" id="sum-mem-total">Total: -- GB</span>
            </div>
            <div class="summary-item">
                <i class="bi bi-box summary-icon text-success"></i>
                <span class="summary-label">Contenedores</span>
                <span class="summary-value" id="sum-cont-running">-- Activos</span>
                <span class="summary-sub" id="sum-cont-total">Total: --</span>
            </div>
            <div class="summary-item">
                <i class="bi bi-images summary-icon text-info"></i>
                <span class="summary-label">Imágenes</span>
                <span class="summary-value" id="sum-img-count">-- Imagenes</span>
                <span class="summary-sub" id="sum-img-size">Tamaño: -- GB</span>
            </div>
        </div>

        <!-- Pestañas -->
        <ul class="nav nav-pills mb-4" id="dockerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="stats-tab" data-bs-toggle="pill" data-bs-target="#stats-pane" type="button" role="tab">
                    <i class="bi bi-speedometer2 me-2"></i> Estadísticas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="list-tab" data-bs-toggle="pill" data-bs-target="#list-pane" type="button" role="tab">
                    <i class="bi bi-list-ul me-2"></i> Contenedores
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="images-tab" data-bs-toggle="pill" data-bs-target="#images-pane" type="button" role="tab">
                    <i class="bi bi-layers me-2"></i> Imágenes
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="compose-tab" data-bs-toggle="pill" data-bs-target="#compose-pane" type="button" role="tab">
                    <i class="bi bi-file-earmark-code me-2"></i> Compose
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="terminal-tab" data-bs-toggle="pill" data-bs-target="#terminal-pane" type="button" role="tab">
                    <i class="bi bi-terminal me-2"></i> Terminal
                </button>
            </li>
        </ul>

        <div class="tab-content" id="dockerTabsContent">
            <!-- Pestaña de Estadísticas -->
            <div class="tab-pane fade show active" id="stats-pane" role="tabpanel">
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th onclick="setSort('stats', 'Name')">Contenedor <i id="sort-stats-Name" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th onclick="setSort('stats', 'CPUPerc')">CPU % <i id="sort-stats-CPUPerc" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th onclick="setSort('stats', 'MemUsage')">MEMORIA / LÍMITE <i id="sort-stats-MemUsage" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th onclick="setSort('stats', 'MemPerc')">MEM % <i id="sort-stats-MemPerc" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th onclick="setSort('stats', 'NetIO')">NET I/O <i id="sort-stats-NetIO" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th onclick="setSort('stats', 'BlockIO')">BLOCK I/O <i id="sort-stats-BlockIO" class="bi bi-arrow-down-up sort-icon"></i></th>
                                </tr>
                            </thead>
                            <tbody id="stats-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Contenedores -->
            <div class="tab-pane fade" id="list-pane" role="tabpanel">
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th onclick="setSort('list', 'Names')">Nombre <i id="sort-list-Names" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th>Id Contenedor</th>
                                    <th onclick="setSort('list', 'Status')">Estado <i id="sort-list-Status" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th onclick="setSort('list', 'Ports')">Puerto <i id="sort-list-Ports" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="list-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Imágenes -->
            <div class="tab-pane fade" id="images-pane" role="tabpanel">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="bi bi-layers me-2"></i> Imágenes Locales</h5>
                        <div>
                            <input type="file" id="import-file" accept=".tar" style="display: none;" onchange="handleImport(this)">
                            <button class="btn btn-primary btn-sm" onclick="document.getElementById('import-file').click()" id="btn-import">
                                <i class="bi bi-upload me-1"></i> Importar Imagen (.tar)
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th onclick="setSort('images', 'Repository')">Repositorio <i id="sort-images-Repository" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th>Tag</th>
                                    <th>ID Imagen</th>
                                    <th>En Uso Por</th>
                                    <th onclick="setSort('images', 'Size')">Tamaño <i id="sort-images-Size" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="images-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Compose -->
            <div class="tab-pane fade" id="compose-pane" role="tabpanel">
                <div class="card p-4">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-4">
                            <select class="form-select bg-dark text-light border-secondary" id="compose-select" onchange="loadCompose(this.value)">
                                <option value="" selected>Seleccionar Proyecto...</option>
                            </select>
                        </div>
                        <div class="col-md-8 text-end">
                            <button class="btn btn-primary btn-sm me-2" id="btn-compose-save" onclick="saveCompose()" disabled>
                                <i class="bi bi-save"></i> Guardar Cambios
                            </button>
                            <!-- <button class="btn btn-success btn-sm me-2" id="btn-compose-up" onclick="handleComposeAction('compose_up')" disabled>
                                <i class="bi bi-arrow-up-circle"></i> Compose Up
                            </button>
                            <button class="btn btn-danger btn-sm" id="btn-compose-down" onclick="handleComposeAction('compose_down')" disabled>
                                <i class="bi bi-arrow-down-circle"></i> Compose Down
                            </button> -->
                        </div>
                    </div>
                    <div class="mb-2">
                        <small class="text-secondary" id="compose-path">Ruta: -</small>
                    </div>
                    <textarea id="compose-editor" style="width: 100%; height: 500px; background-color: #1e1e1e; color: #d4d4d4; font-family: 'Consolas', monospace; padding: 15px; border-radius: 8px; border: 1px solid #334155; outline: none; font-size: 0.95rem;" spellcheck="false" disabled></textarea>
                </div>
            </div>

            <!-- Pestaña de Terminal -->
            <div class="tab-pane fade" id="terminal-pane" role="tabpanel">
                <div class="card p-4">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-4">
                            <select class="form-select bg-dark text-light border-secondary" id="console-select">
                                <option value="" selected>Seleccionar Contenedor...</option>
                            </select>
                        </div>
                        <div class="col-md-8 text-end">
                            <button class="btn btn-info btn-sm me-2" onclick="disconnectConsole()" id="btn-disconnect" disabled>
                                <i class="bi bi-plug"></i> Desconectar
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="clearConsole()">
                                <i class="bi bi-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="terminal-window" id="terminal-window">
                        <div class="terminal-output" id="terminal-output">
                            <span class="text-secondary"># Selecciona un contenedor para conectar la terminal...</span>
                        </div>
                        <div class="terminal-input-line">
                            <span class="prompt" id="prompt-label">root@docker:/#</span>
                            <input type="text" class="cmd-input" id="cmd-input" placeholder="Escribe un comando..." autocomplete="off" disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    <div class="modal fade" id="logsModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="logsModalLabel"><i class="bi bi-terminal me-2"></i> Logs</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="logs-content"></div></div></div></div></div>
    <div class="modal fade" id="inspectModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="inspectModalLabel"><i class="bi bi-search me-2"></i> Inspección</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="inspect-content"></div></div></div></div></div>
    <div class="modal fade" id="topModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="topModalLabel"><i class="bi bi-list-task me-2"></i> Procesos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="top-content" style="background-color:#000; color:#50fa7b; font-family:monospace; padding:15px; border-radius:8px; white-space:pre; overflow-x:auto;"></div></div></div></div></div>
    <div class="modal fade" id="diffModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="diffModalLabel"><i class="bi bi-file-earmark-diff me-2"></i> Cambios FS</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="diff-content" style="background-color:#000; color:#ffb86c; font-family:monospace; padding:15px; border-radius:8px; white-space:pre; overflow-x:auto;"></div></div></div></div></div>
    <div class="modal fade" id="historyModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="historyModalLabel"><i class="bi bi-clock-history me-2"></i> Historial</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="history-content" style="background-color:#000; color:#d1d5db; font-family:monospace; padding:15px; border-radius:8px; white-space:pre; overflow-x:auto;"></div></div></div></div></div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        let statsData = [], listData = [], imagesData = [];
        let sortConfigs = {
            stats: { key: 'Name', direction: 'asc' },
            list: { key: 'Names', direction: 'asc' },
            images: { key: 'Repository', direction: 'asc' }
        };
        const logsModal = new bootstrap.Modal(document.getElementById('logsModal'));
        const inspectModal = new bootstrap.Modal(document.getElementById('inspectModal'));
        const topModal = new bootstrap.Modal(document.getElementById('topModal'));
        const diffModal = new bootstrap.Modal(document.getElementById('diffModal'));
        const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));

        let consoleHistory = [], historyIndex = -1, currentWorkdir = '/', currentContainer = null;
        let currentComposePath = null, currentComposeContainerId = null;

        async function updateData() {
            try {
                await Promise.all([fetchStats(), fetchList(), fetchImages(), fetchInfo()]);
                renderAll();
                updateConsoleSelect();
                updateComposeSelect();
            } catch (e) { console.error(e); }
            finally { setTimeout(updateData, 3000); }
        }

        async function fetchStats() { try { const res = await fetch('stats.php', {headers:{'X-Requested-With':'XMLHttpRequest'}}); if(res.status===401) window.location.reload(); statsData = await res.json(); } catch(e){} }
        async function fetchList() { try { const res = await fetch('ps.php', {headers:{'X-Requested-With':'XMLHttpRequest'}}); if(res.status===401) window.location.reload(); listData = await res.json(); } catch(e){} }
        async function fetchImages() { try { const res = await fetch('images.php', {headers:{'X-Requested-With':'XMLHttpRequest'}}); if(res.status===401) window.location.reload(); imagesData = await res.json(); } catch(e){} }
        async function fetchInfo() {
            try {
                const res = await fetch('info.php', {headers:{'X-Requested-With':'XMLHttpRequest'}});
                if(res.status===401) window.location.reload();
                const d = await res.json();
                document.getElementById('sum-cpu-usage').innerText = `${d.host_load['1m'].toFixed(2)} (Load)`;
                document.getElementById('sum-os-info').innerText = `${d.os} (${d.cpus} CPUs)`;
                document.getElementById('sum-mem-usage').innerText = `${(d.host_mem.used/(1024**3)).toFixed(2)} GB`;
                document.getElementById('sum-mem-total').innerText = `Total: ${(d.host_mem.total/(1024**3)).toFixed(2)} GB (${d.host_mem.percent}%)`;
                document.getElementById('sum-cont-running').innerText = `${d.containers.running} Activos`;
                document.getElementById('sum-cont-total').innerText = `Total: ${d.containers.total} (${d.containers.stopped} off)`;
                document.getElementById('sum-img-count').innerText = `${d.images.count} Imágenes`;
                document.getElementById('sum-img-size').innerText = `Tamaño: ${d.images.size}`;
            } catch(e){}
        }

        function renderAll() { renderStats(); renderList(); renderImages(); }

        function parseValue(v) {
            if (typeof v !== 'string') return v;
            const clean = v.replace(/[%\s]/g, '').toLowerCase();
            const units = { 'b': 1, 'kb': 1024, 'mb': 1024**2, 'gb': 1024**3, 'tb': 1024**4 };
            const m = v.toLowerCase().match(/^([\d.]+)\s*([a-z]+)/);
            if (m && units[m[2]]) return parseFloat(m[1]) * units[m[2]];
            return isNaN(clean) ? clean : parseFloat(clean);
        }

        function renderStats() {
            const body = document.getElementById('stats-body');
            const sorted = [...statsData].sort((a,b) => {
                const vA = parseValue(a[sortConfigs.stats.key]), vB = parseValue(b[sortConfigs.stats.key]);
                return sortConfigs.stats.direction === 'asc' ? (vA > vB ? 1 : -1) : (vA < vB ? 1 : -1);
            });
            body.innerHTML = sorted.map(c => `<tr class="${parseValue(c.CPUPerc)>80?'row-danger':''}"><td><b>${c.Name}</b></td><td><span class="badge bg-dark border border-info text-info">${c.CPUPerc}</span></td><td>${c.MemUsage}</td><td><div class="progress" style="height:6px;width:60px;"><div class="progress-bar bg-info" style="width:${c.MemPerc}"></div></div><small>${c.MemPerc}</small></td><td>${c.NetIO}</td><td>${c.BlockIO}</td></tr>`).join('');
        }

        function renderList() {
            const body = document.getElementById('list-body');
            const sorted = [...listData].sort((a,b) => {
                let vA = a[sortConfigs.list.key], vB = b[sortConfigs.list.key];
                if(sortConfigs.list.key === 'Ports') {
                    const getP = (s) => { const m = (s||'').match(/:(\d+)->/); return m ? parseInt(m[1]) : 0; };
                    vA = getP(vA); vB = getP(vB);
                } else { vA = (vA||'').toLowerCase(); vB = (vB||'').toLowerCase(); }
                return sortConfigs.list.direction === 'asc' ? (vA > vB ? 1 : -1) : (vA < vB ? 1 : -1);
            });
            body.innerHTML = sorted.map(c => {
                const isUp = c.Status.toLowerCase().includes('up'), isPaused = c.Status.toLowerCase().includes('paused');
                const ports = (c.Ports || '').split(',').map(p => {
                    const m = p.trim().match(/:(\d+)->(\d+)\/(\w+)/);
                    return m ? {h:m[1], c:m[2]} : null;
                }).filter(p=>p);
                const openBtns = ports.map(p => `<a href="http://${window.location.hostname}:${p.h}" target="_blank" class="btn btn-indigo btn-action"><i class="bi bi-box-arrow-up-right"></i></a>`).join('');
                return `<tr>
                    <td><b class="text-info">${c.Names}</b></td>
                    <td><code>${c.ID}</code></td>
                    <td><i class="bi bi-circle-fill me-2 ${isUp?'status-up':'status-down'}"></i>${c.Status}</td>
                    <td>${ports.map(p=>`<div><code>${p.h}</code>-><code>${p.c}</code></div>`).join('') || '-'}</td>
                    <td class="text-nowrap">
                        <button class="btn btn-info btn-action" onclick="showLogs('${c.ID}')"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-teal btn-action" onclick="handleAction('${c.ID}', 'inspect', this)"><i class="bi bi-search"></i></button>
                        <button class="btn btn-secondary btn-action" onclick="showTop('${c.ID}')"><i class="bi bi-list-task"></i></button>
                        <button class="btn btn-orange btn-action" onclick="showDiff('${c.ID}')"><i class="bi bi-file-earmark-diff"></i></button>
                        <button class="btn btn-primary btn-action" onclick="handleAction('${c.ID}', 'restart', this)"><i class="bi bi-arrow-clockwise"></i></button>
                        <button class="btn btn-success btn-action" onclick="handleAction('${c.ID}', 'set_restart', this)"><i class="bi bi-shield-check"></i></button>
                        ${isUp ? (isPaused ? `<button class="btn btn-success btn-action" onclick="handleAction('${c.ID}', 'unpause', this)"><i class="bi bi-play-circle"></i></button>` : `<button class="btn btn-warning btn-action" onclick="handleAction('${c.ID}', 'pause', this)"><i class="bi bi-pause-circle"></i></button>`) : ''}
                        ${isUp ? `<button class="btn btn-danger btn-action" onclick="handleAction('${c.ID}', 'stop', this)"><i class="bi bi-stop-fill"></i></button>` : `<button class="btn btn-success btn-action" onclick="handleAction('${c.ID}', 'start', this)"><i class="bi bi-play-fill"></i></button>`}
                        <button class="btn btn-magenta btn-action" onclick="handleAction('${c.ID}', 'rm', this)"><i class="bi bi-trash"></i></button>
                        ${openBtns}
                    </td>
                </tr>`;
            }).join('');
        }

        function renderImages() {
            const body = document.getElementById('images-body');
            body.innerHTML = imagesData.map(i => `<tr><td><b>${i.Repository}</b></td><td><span class="badge bg-secondary">${i.Tag}</span></td><td><code>${i.ID}</code></td><td>${listData.filter(c=>c.Image.includes(i.ID)).map(c=>`<span class="badge bg-info text-dark">${c.Names}</span>`).join('')}</td><td>${i.Size}</td><td class="text-nowrap"><button class="btn btn-info btn-action" onclick="showHistory('${i.ID}')"><i class="bi bi-clock-history"></i></button><button class="btn btn-light btn-action" onclick="openGitHub('${i.Repository}','${i.ID}')"><i class="bi bi-github"></i></button><a href="download_image.php?id=${i.ID}&repo=${i.Repository}&tag=${i.Tag}" class="btn btn-success btn-action"><i class="bi bi-download"></i></a><button class="btn btn-magenta btn-action" onclick="handleAction('${i.ID}', 'rmi', this)"><i class="bi bi-trash"></i></button></td></tr>`).join('');
        }

        function updateConsoleSelect() {
            const s = document.getElementById('console-select');
            const running = listData.filter(c => c.Status.toLowerCase().includes('up')).map(c => c.Names.startsWith('/')?c.Names.substring(1):c.Names);
            const sig = running.sort().join('|');
            if (s.dataset.signature === sig) return;
            s.dataset.signature = sig;
            const val = s.value;
            s.innerHTML = '<option value="">Seleccionar Contenedor...</option>' + running.map(n => `<option value="${n}" ${n===val?'selected':''}>${n}</option>`).join('');
        }

        function updateComposeSelect() {
            const s = document.getElementById('compose-select');
            const sig = listData.map(c=>c.Names).sort().join('|');
            if (s.dataset.signature === sig) return;
            s.dataset.signature = sig;
            const val = s.value;
            s.innerHTML = '<option value="">Seleccionar Proyecto...</option>' + listData.map(c => `<option value="${c.ID}" ${c.ID===val?'selected':''}>${c.Names}</option>`).join('');
        }

        async function loadCompose(id) {
            if(!id) return resetComposeEditor();
            currentComposeContainerId = id;
            try {
                const res = await fetch(`get_compose.php?id=${id}`);
                const data = await res.json();
                if(data.error) return alert(data.error);
                currentComposePath = data.path;
                document.getElementById('compose-path').innerText = `Ruta: ${data.path}`;
                document.getElementById('compose-editor').value = data.content;
                document.getElementById('compose-editor').disabled = false;
                ['btn-compose-save','btn-compose-up','btn-compose-down'].forEach(id=>document.getElementById(id).disabled=false);
            } catch(e){alert("Error al cargar");}
        }

        function resetComposeEditor() {
            currentComposePath = null; currentComposeContainerId = null;
            document.getElementById('compose-path').innerText = "Ruta: -";
            document.getElementById('compose-editor').value = "";
            document.getElementById('compose-editor').disabled = true;
            ['btn-compose-save','btn-compose-up','btn-compose-down'].forEach(id=>document.getElementById(id).disabled=true);
        }

        async function saveCompose() {
            const btn = document.getElementById('btn-compose-save');
            btn.disabled = true;
            try {
                const res = await fetch('save_compose.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({path:currentComposePath, content:document.getElementById('compose-editor').value})});
                const d = await res.json();
                alert(d.success?"Guardado":"Error: "+d.error);
            } catch(e){alert("Error");} finally {btn.disabled = false;}
        }

        async function handleComposeAction(action) {
            const btn = document.getElementById(action==='compose_up'?'btn-compose-up':'btn-compose-down');
            const old = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            try {
                const res = await fetch('manage.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:currentComposeContainerId, action:action})});
                const d = await res.json();
                alert(d.success?d.output:d.error);
                updateData();
            } catch(e){alert("Error");} finally {btn.disabled=false; btn.innerHTML=old;}
        }

        async function handleAction(id, action, btn) {
            if((action==='rm'||action==='rmi') && !confirm('¿Seguro?')) return;
            const old = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            try {
                const res = await fetch('manage.php', {method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}, body:JSON.stringify({id, action})});
                if(res.status===401) window.location.reload();
                const d = await res.json();
                if(d.success) { await Promise.all([fetchStats(), fetchList(), fetchImages(), fetchInfo()]); renderAll(); }
                else alert("Error: "+d.error);
            } catch(e){alert("Error");} finally {btn.disabled=false; btn.innerHTML=old;}
        }

        function clearConsole() { document.getElementById('terminal-output').innerHTML = ''; }
        function disconnectConsole() { currentContainer = null; document.getElementById('cmd-input').disabled = true; }

        async function showLogs(id) {
            logsModal.show(); document.getElementById('logs-content').innerText = "Cargando...";
            const res = await fetch('manage.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id, action:'logs'})});
            const d = await res.json(); document.getElementById('logs-content').innerText = d.output;
        }

        async function showTop(id) {
            topModal.show(); document.getElementById('top-content').innerText = "Cargando...";
            const res = await fetch('manage.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id, action:'top'})});
            const d = await res.json(); document.getElementById('top-content').innerText = d.output;
        }

        async function showDiff(id) {
            diffModal.show(); document.getElementById('diff-content').innerText = "Cargando...";
            const res = await fetch('manage.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id, action:'diff'})});
            const d = await res.json(); document.getElementById('diff-content').innerText = d.output;
        }

        async function showHistory(id) {
            historyModal.show(); document.getElementById('history-content').innerText = "Cargando...";
            const res = await fetch('manage.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id, action:'history'})});
            const d = await res.json(); document.getElementById('history-content').innerText = d.output;
        }

        async function handleImport(input) {
            const file = input.files[0]; if(!file) return;
            const btn = document.getElementById('btn-import'); const old = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = 'Subiendo...';
            const fd = new FormData(); fd.append('image_tar', file);
            const res = await fetch('upload_image.php', {method:'POST', body:fd});
            const d = await res.json();
            alert(d.success?d.output:d.error);
            btn.disabled = false; btn.innerHTML = old; input.value = ''; updateData();
        }

        function setSort(t,k) {
            if(sortConfigs[t].key===k) sortConfigs[t].direction=sortConfigs[t].direction==='asc'?'desc':'asc';
            else {sortConfigs[t].key=k; sortConfigs[t].direction='asc';}
            renderAll();
        }

        document.getElementById('console-select').addEventListener('change', function(){ 
            currentContainer = this.value;
            document.getElementById('cmd-input').disabled = !this.value;
            document.getElementById('btn-disconnect').disabled = !this.value;
            if(this.value) {
                document.getElementById('terminal-output').innerHTML = `<span class="text-success">Conectado a ${this.value}</span>\n`;
                document.getElementById('cmd-input').focus();
            }
        });

        document.getElementById('cmd-input').addEventListener('keydown', async function(e){
            if(e.key==='Enter') {
                const cmd = this.value.trim(); if(!cmd) return;
                this.value = '';
                document.getElementById('terminal-output').innerHTML += `root@docker:# ${cmd}\n`;
                const res = await fetch('console.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:currentContainer, command:cmd, workdir:'/'})});
                const d = await res.json();
                document.getElementById('terminal-output').innerHTML += d.output + '\n';
                document.getElementById('terminal-window').scrollTop = document.getElementById('terminal-window').scrollHeight;
            }
        });

        updateData();
    </script>
</body>
</html>

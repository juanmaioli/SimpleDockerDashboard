<?php require_once 'auth.php'; checkAuth(); ?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐳 Simple Docker Dashboard</title>
    <!-- Favicon bi-box-seam text-info (SVG Dinámico) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%230dcaf0'><path d='M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 6.404l6.154-2.462zm3.25 3.63-6.5 2.6v7.922l6.5-2.6V6.17zm-7.5 10.522V8.77L.5 6.17v7.922zM7.5.582a1 1 0 0 1 .736 0l7.67 3.068A.5.5 0 0 1 16 4.115v8.93a1 1 0 0 1-.607.922l-7 2.8a1 1 0 0 1-.786 0l-7-2.8A1 1 0 0 1 0 13.045V4.115a.5.5 0 0 1 .308-.465z'/></svg>">
    <!-- Bootstrap 5.3 + Iconos -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
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
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
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
                                    <th>Imagen</th>
                                    <th onclick="setSort('list', 'Status')">Estado <i id="sort-list-Status" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th>Puertos</th>
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
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th onclick="setSort('images', 'Repository')">Repositorio <i id="sort-images-Repository" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th>Tag</th>
                                    <th>ID Imagen</th>
                                    <th>En Uso Por</th>
                                    <th onclick="setSort('images', 'Size')">Tamaño <i id="sort-images-Size" class="bi bi-arrow-down-up sort-icon"></i></th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="images-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pestaña de Terminal -->
            <div class="tab-pane fade" id="terminal-pane" role="tabpanel">
                <div class="card p-4">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-4">
                            <select class="form-select bg-dark text-light border-secondary" id="console-select">
                                <option value="" selected>Seleccionar Contenedor...</option>
                                <!-- Se llena dinámicamente -->
                            </select>
                        </div>
                        <div class="col-md-8 text-end">
                            <button class="btn btn-outline-info btn-sm me-2" onclick="disconnectConsole()" id="btn-disconnect" disabled>
                                <i class="bi bi-plug"></i> Desconectar
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="clearConsole()">
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

    <!-- Modal para Logs -->
    <div class="modal fade" id="logsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logsModalLabel"><i class="bi bi-terminal me-2"></i> Logs del Contenedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="logs-content">Cargando logs...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Inspección -->
    <div class="modal fade" id="inspectModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inspectModalLabel"><i class="bi bi-search me-2"></i> Inspección del Contenedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="inspect-content">Cargando datos técnicos...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Historial -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel"><i class="bi bi-clock-history me-2"></i> Historial de la Imagen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="history-content" style="background-color: #000; color: #d1d5db; font-family: 'Courier New', Courier, monospace; padding: 15px; border-radius: 8px; max-height: 500px; overflow-y: auto; white-space: pre; font-size: 0.85rem;">Cargando historial...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script>
        let statsData = [];
        let listData = [];
        let imagesData = [];
        let sortConfigs = {
            stats: { key: 'Name', direction: 'asc' },
            list: { key: 'Names', direction: 'asc' },
            images: { key: 'Repository', direction: 'asc' }
        };
        const logsModal = new bootstrap.Modal(document.getElementById('logsModal'));
        const inspectModal = new bootstrap.Modal(document.getElementById('inspectModal'));
        const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));

        // Terminal Vars
        let consoleHistory = [];
        let historyIndex = -1;
        let currentWorkdir = '/';
        let currentContainer = null;

        async function updateData() {
            await Promise.all([fetchStats(), fetchList(), fetchImages()]);
            renderAll();
            updateConsoleSelect();
        }

        async function fetchStats() {
            try { const res = await fetch('stats.php'); statsData = await res.json(); } catch (e) { console.error(e); }
        }

        async function fetchList() {
            try { const res = await fetch('ps.php'); listData = await res.json(); } catch (e) { console.error(e); }
        }

        async function fetchImages() {
            try { const res = await fetch('images.php'); imagesData = await res.json(); } catch (e) { console.error(e); }
        }

        function updateConsoleSelect() {
            const select = document.getElementById('console-select');
            if (select.options.length <= 1 && listData.length > 0) {
                listData.forEach(c => {
                    if (c.Status.includes('Up')) {
                        const opt = document.createElement('option');
                        opt.value = c.Names; 
                        opt.innerText = `${c.Names}`;
                        select.appendChild(opt);
                    }
                });
            }
        }

        document.getElementById('console-select').addEventListener('change', function() {
            connectToContainer(this.value);
        });

        function connectToContainer(name) {
            currentContainer = name;
            const input = document.getElementById('cmd-input');
            const output = document.getElementById('terminal-output');
            const btnDisconnect = document.getElementById('btn-disconnect');
            
            if (currentContainer) {
                input.disabled = false;
                btnDisconnect.disabled = false;
                input.focus();
                output.innerHTML = `<span class="terminal-msg-success">Sesión iniciada en contenedor: ${currentContainer}</span>\n`;
                currentWorkdir = '/';
                updatePrompt();
            } else {
                disconnectConsole();
            }
        }

        function disconnectConsole() {
            currentContainer = null;
            currentWorkdir = '/';
            const input = document.getElementById('cmd-input');
            const output = document.getElementById('terminal-output');
            const select = document.getElementById('console-select');
            const btnDisconnect = document.getElementById('btn-disconnect');
            
            input.disabled = true;
            input.value = '';
            btnDisconnect.disabled = true;
            select.value = "";
            output.innerHTML += '<span class="terminal-msg-warning">Conexión cerrada.</span>\n';
            document.getElementById('prompt-label').innerText = "root@docker:/#";
        }

        document.getElementById('cmd-input').addEventListener('keydown', async function(e) {
            if (e.key === 'Enter') {
                const cmd = this.value.trim();
                if (!cmd) return;
                
                this.value = '';
                consoleHistory.push(cmd);
                historyIndex = consoleHistory.length;

                appendOutput(`<span class="prompt">${document.getElementById('prompt-label').innerText}</span> <span style="color:#50fa7b">${cmd}</span>`);

                if (cmd === 'clear') {
                    document.getElementById('terminal-output').innerHTML = '';
                    return;
                }

                if (cmd === 'exit') {
                    disconnectConsole();
                    return;
                }

                try {
                    const res = await fetch('console.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            id: currentContainer,
                            command: cmd,
                            workdir: currentWorkdir
                        })
                    });
                    const data = await res.json();
                    
                    if (data.newWorkdir) {
                        currentWorkdir = data.newWorkdir.trim();
                        updatePrompt();
                    } else if (data.output) {
                        appendOutput(data.output);
                    }
                } catch (err) {
                    appendOutput(`<span class="terminal-msg-danger">Error de red: ${err.message}</span>`);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (historyIndex > 0) {
                    historyIndex--;
                    this.value = consoleHistory[historyIndex];
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (historyIndex < consoleHistory.length - 1) {
                    historyIndex++;
                    this.value = consoleHistory[historyIndex];
                } else {
                    historyIndex = consoleHistory.length;
                    this.value = '';
                }
            }
        });

        function appendOutput(text) {
            const out = document.getElementById('terminal-output');
            out.innerHTML += text + '\n';
            document.getElementById('terminal-window').scrollTop = document.getElementById('terminal-window').scrollHeight;
        }

        function updatePrompt() {
            document.getElementById('prompt-label').innerText = `root@${currentContainer}:${currentWorkdir}#`;
        }

        function clearConsole() {
            document.getElementById('terminal-output').innerHTML = '';
            document.getElementById('cmd-input').focus();
        }

        async function handleAction(id, action, btn) {
            const confirmMsg = action === 'rm' ? '¿Borrar contenedor?' : (action === 'rmi' ? '¿Borrar imagen?' : null);
            if (confirmMsg && !confirm(confirmMsg)) return;
            
            if (action === 'logs') { showLogs(id); return; }
            if (action === 'inspect') { showInspect(id); return; }

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;

            try {
                const res = await fetch('manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, action })
                });
                const data = await res.json();
                if (data.success) await updateData();
                else alert("Error: " + data.error);
            } catch (e) { alert("Error."); }
            finally { btn.disabled = false; btn.innerHTML = originalHtml; }
        }

        async function showLogs(id) {
            document.getElementById('logsModalLabel').innerText = `Logs: ${id}`;
            document.getElementById('logs-content').innerText = "Cargando...";
            logsModal.show();
            try {
                const res = await fetch('manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, action: 'logs' })
                });
                const data = await res.json();
                document.getElementById('logs-content').innerText = data.output || "Sin logs.";
            } catch (e) { document.getElementById('logs-content').innerText = "Error."; }
        }

        async function showInspect(id) {
            document.getElementById('inspectModalLabel').innerText = `Inspección: ${id}`;
            document.getElementById('inspect-content').innerText = "Cargando JSON...";
            inspectModal.show();
            try {
                const res = await fetch('manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, action: 'inspect' })
                });
                const data = await res.json();
                // Formateamos el JSON para que sea legible
                try {
                    const obj = JSON.parse(data.output);
                    document.getElementById('inspect-content').innerText = JSON.stringify(obj, null, 2);
                } catch(e) {
                    document.getElementById('inspect-content').innerText = data.output;
                }
            } catch (e) { document.getElementById('inspect-content').innerText = "Error."; }
        }

        async function showHistory(id) {
            document.getElementById('historyModalLabel').innerText = `Historial: ${id}`;
            document.getElementById('history-content').innerText = "Cargando historial...";
            historyModal.show();
            try {
                const res = await fetch('manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, action: 'history' })
                });
                const data = await res.json();
                document.getElementById('history-content').innerText = data.output || "Sin historial disponible.";
            } catch (e) { document.getElementById('history-content').innerText = "Error."; }
        }

        function setSort(type, key) {
            if (sortConfigs[type].key === key) sortConfigs[type].direction = sortConfigs[type].direction === 'asc' ? 'desc' : 'asc';
            else { sortConfigs[type].key = key; sortConfigs[type].direction = 'asc'; }
            updateSortIcons(); renderAll();
        }

        function updateSortIcons() {
            document.querySelectorAll('.sort-icon').forEach(i => i.className = 'bi bi-arrow-down-up sort-icon');
            ['stats', 'list', 'images'].forEach(type => {
                const icon = document.getElementById(`sort-${type}-${sortConfigs[type].key}`);
                if (icon) icon.className = `bi bi-sort-numeric-${sortConfigs[type].direction === 'asc' ? 'down' : 'up'} sort-icon sort-active`;
            });
        }

        function parseValue(val) {
            if (typeof val !== 'string') return val;
            const cleanVal = val.replace(/[%\s]/g, '').toLowerCase();
            const units = { 'b': 1, 'kb': 1024, 'mb': 1024**2, 'gb': 1024**3, 'tb': 1024**4 };
            const match = val.toLowerCase().match(/^([\d.]+)\s*([a-z]+)/);
            if (match && units[match[2]]) return parseFloat(match[1]) * units[match[2]];
            return isNaN(cleanVal) ? cleanVal : parseFloat(cleanVal);
        }

        function renderAll() { renderStats(); renderList(); renderImages(); }

        function renderStats() {
            const body = document.getElementById('stats-body');
            const sorted = [...statsData].sort((a, b) => {
                const vA = parseValue(a[sortConfigs.stats.key]);
                const vB = parseValue(b[sortConfigs.stats.key]);
                return sortConfigs.stats.direction === 'asc' ? (vA > vB ? 1 : -1) : (vA < vB ? 1 : -1);
            });
            body.innerHTML = sorted.map(c => {
                const cpuVal = parseValue(c.CPUPerc);
                const memVal = parseValue(c.MemPerc);
                let rowClass = "";
                let badgeClass = "bg-dark border-info text-info";

                if (cpuVal > 80 || memVal > 80) {
                    rowClass = "row-danger";
                    badgeClass = "bg-danger text-white";
                } else if (cpuVal > 50 || memVal > 50) {
                    rowClass = "row-warning";
                    badgeClass = "bg-warning text-dark";
                }

                return `
                <tr class="${rowClass}">
                    <td><div class="d-flex align-items-center"><i class="bi bi-box-seam text-info me-2"></i><span class="fw-bold">${c.Name}</span></div></td>
                    <td><span class="badge border ${badgeClass}">${c.CPUPerc}</span></td>
                    <td><span class="text-tiny">${c.MemUsage}</span></td>
                    <td>
                        <div class="progress" style="height: 6px; width: 60px;">
                            <div class="progress-bar ${cpuVal > 80 ? 'bg-danger' : (cpuVal > 50 ? 'bg-warning' : 'bg-info')}" style="width: ${c.MemPerc}"></div>
                        </div>
                        <small class="text-secondary text-tiny">${c.MemPerc}</small>
                    </td>
                    <td><span class="text-tiny">${c.NetIO}</span></td>
                    <td><span class="text-tiny">${c.BlockIO}</span></td>
                </tr>
            `}).join('');
        }

        function renderList() {
            const body = document.getElementById('list-body');
            const sorted = [...listData].sort((a, b) => {
                const vA = a[sortConfigs.list.key].toLowerCase();
                const vB = b[sortConfigs.list.key].toLowerCase();
                return sortConfigs.list.direction === 'asc' ? (vA > vB ? 1 : -1) : (vA < vB ? 1 : -1);
            });
            body.innerHTML = sorted.map(c => {
                const isUp = c.Status.includes('Up');
                return `
                <tr>
                    <td><span class="fw-bold text-info">${c.Names}</span><br><small class="text-secondary text-tiny">${c.ID}</small></td>
                    <td><code class="text-light text-tiny">${c.Image}</code></td>
                    <td><i class="bi bi-circle-fill me-2 ${isUp ? 'status-up' : 'status-down'}" style="font-size: 0.7rem;"></i><span class="text-tiny">${c.Status}</span></td>
                    <td><small class="text-tiny">${c.Ports || '-'}</small></td>
                    <td class="text-nowrap">
                        <button class="btn btn-outline-info btn-action" onclick="handleAction('${c.ID}', 'logs', this)" title="Logs"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-outline-warning btn-action" onclick="handleAction('${c.ID}', 'inspect', this)" title="Inspeccionar"><i class="bi bi-search"></i></button>
                        ${isUp ? `<button class="btn btn-outline-danger btn-action" onclick="handleAction('${c.ID}', 'stop', this)" title="Stop"><i class="bi bi-stop-fill"></i></button>` 
                               : `<button class="btn btn-outline-success btn-action" onclick="handleAction('${c.ID}', 'start', this)" title="Start"><i class="bi bi-play-fill"></i></button>`}
                        <button class="btn btn-outline-secondary btn-action" onclick="handleAction('${c.ID}', 'rm', this)" title="Borrar"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `}).join('');
        }

        function renderImages() {
            const body = document.getElementById('images-body');
            const sorted = [...imagesData].sort((a, b) => {
                const vA = parseValue(a[sortConfigs.images.key]);
                const vB = parseValue(b[sortConfigs.images.key]);
                return sortConfigs.images.direction === 'asc' ? (vA > vB ? 1 : -1) : (vA < vB ? 1 : -1);
            });
            
            body.innerHTML = sorted.map(i => {
                const fullRepoTag = i.Repository + ":" + i.Tag;
                const usedBy = listData.filter(c => {
                    // Coincidencia por nombre completo (repo:tag)
                    if (c.Image === fullRepoTag) return true;
                    // Coincidencia si el contenedor no tiene tag pero el repo coincide (asumiendo latest)
                    if (c.Image === i.Repository && i.Tag === "latest") return true;
                    // Coincidencia por ID (el ID en ps suele ser corto, ej: 12 caracteres)
                    if (i.ID.startsWith(c.Image) || c.Image.startsWith(i.ID)) return true;
                    return false;
                }).map(c => c.Names);
                return `
                <tr>
                    <td><span class="fw-bold text-info">${i.Repository}</span></td>
                    <td><span class="badge bg-secondary">${i.Tag}</span></td>
                    <td><code class="text-secondary small">${i.ID}</code></td>
                    <td>
                        <div class="badge-container">
                            ${usedBy.length > 0 
                                ? usedBy.map(name => `<span class="badge bg-info text-dark text-tiny">${name}</span>`).join('')
                                : '<small class="text-secondary text-tiny italic">Ninguno</small>'
                            }
                        </div>
                    </td>
                    <td><span class="badge bg-dark border border-secondary">${i.Size}</span></td>
                    <td>
                        <button class="btn btn-outline-info btn-action" onclick="showHistory('${i.ID}')" title="Historial"><i class="bi bi-clock-history"></i></button>
                        <button class="btn btn-outline-danger btn-action" onclick="handleAction('${i.ID}', 'rmi', this)" title="Borrar Imagen"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `}).join('');
        }

        updateSortIcons(); updateData(); setInterval(updateData, 3000);
    </script>
</body>
</html>

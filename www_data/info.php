<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

// Función para obtener memoria del host (Linux)
function getHostMemory() {
    $data = explode("\n", file_get_contents("/proc/meminfo"));
    $memInfo = [];
    foreach ($data as $line) {
        if (empty($line)) continue;
        list($key, $val) = explode(":", $line);
        $memInfo[$key] = trim($val);
    }
    
    $total = (int) filter_var($memInfo['MemTotal'], FILTER_SANITIZE_NUMBER_INT) * 1024;
    $free = (int) filter_var($memInfo['MemFree'], FILTER_SANITIZE_NUMBER_INT) * 1024;
    $available = (int) filter_var($memInfo['MemAvailable'], FILTER_SANITIZE_NUMBER_INT) * 1024;
    $used = $total - $available;
    
    return [
        'total' => $total,
        'used' => $used,
        'free' => $free,
        'available' => $available,
        'percent' => round(($used / $total) * 100, 2)
    ];
}

// Función para obtener carga de CPU (Linux)
function getHostCpuLoad() {
    $load = sys_getloadavg();
    return [
        '1m' => $load[0],
        '5m' => $load[1],
        '15m' => $load[2]
    ];
}

// Obtener info de Docker
$dockerInfo = json_decode(shell_exec('docker info --format "{{json .}}"'), true);

// docker system df --format "{{json .}}" devuelve objetos JSON por línea
$dfOutput = shell_exec('docker system df --format "{{json .}}"');
$imagesSize = "0B";

if ($dfOutput) {
    $dfLines = explode("\n", trim($dfOutput));
    foreach ($dfLines as $line) {
        $item = json_decode($line, true);
        if ($item && isset($item['Type']) && $item['Type'] === 'Images') {
            $imagesSize = $item['Size'];
            break;
        }
    }
} else {
    // Intentar obtener de la salida plana si falla el JSON
    $dfRaw = shell_exec('docker system df');
    if (preg_match('/Images\s+\d+\s+([\d.]+\w+)/', $dfRaw, $matches)) {
        $imagesSize = $matches[1];
    }
}

$response = [
    'os' => $dockerInfo['OperatingSystem'] ?? php_uname('s'),
    'kernel' => $dockerInfo['KernelVersion'] ?? php_uname('r'),
    'docker_version' => $dockerInfo['ServerVersion'] ?? 'Unknown',
    'cpus' => $dockerInfo['NCPU'] ?? 0,
    'mem_total' => $dockerInfo['MemTotal'] ?? 0,
    'containers' => [
        'total' => $dockerInfo['Containers'] ?? 0,
        'running' => $dockerInfo['ContainersRunning'] ?? 0,
        'stopped' => $dockerInfo['ContainersStopped'] ?? 0,
        'paused' => $dockerInfo['ContainersPaused'] ?? 0,
    ],
    'images' => [
        'count' => $dockerInfo['Images'] ?? 0,
        'size' => $imagesSize
    ],
    'host_mem' => getHostMemory(),
    'host_load' => getHostCpuLoad(),
    'hostname' => $dockerInfo['Name'] ?? gethostname()
];

echo json_encode($response);

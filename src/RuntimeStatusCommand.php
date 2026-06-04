<?php

/**
 * This file is part of the Nexph Framework.
 *
 * (c) Nexphlabs <https://github.com/nexphlabs>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Nexph\Cli;

use Nexph\Runtime\Runtime;
use Nexph\Runtime\Observability\RuntimeMetrics;
use Nexph\Runtime\Observability\HealthMonitor;
use Nexph\Runtime\Observability\Dashboard;
use Nexph\Runtime\Observability\RuntimeState;

/**
 * Runtime status command.
 */
class RuntimeStatusCommand extends Command {
    protected string $name = 'runtime:status';
    protected string $description = 'Display runtime status and health';
    
    public function execute(array $args = []): int {
        $parsed = $this->parseArgs($args);
        $options = $parsed['options'];
        
        $verbose = isset($options['verbose']) || isset($options['v']);
        $json = isset($options['json']);
        
        try {
            $metrics = new RuntimeMetrics();
            $health = new HealthMonitor();
            $dashboard = new Dashboard($metrics, $health);
            $state = RuntimeState::snapshot($options['driver'] ?? null);
            
            $snapshot = $dashboard->getSnapshot();
            
            if ($json) {
                echo json_encode($snapshot, JSON_PRETTY_PRINT) . "\n";
            } elseif ($verbose) {
                echo $dashboard->render();
            } else {
                echo sprintf("%s | mode=%s queue=%d dlq=%d workers=%d mem=%.1fMB cpu=%.2f degraded=%s\n", $dashboard->renderCompact(), $state['runtime']['mode'], $state['queue']['depth'], $state['queue']['dead_letters'], $state['queue']['workers'], $state['computed']['memory_usage_mb'], (float)($state['system']['cpu_load'][0] ?? 0), $state['runtime']['degradation_state']);
            }
            
            return 0;
            
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}

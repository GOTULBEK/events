<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\RenderTextFormat;
use Prometheus\Gauge;
use Prometheus\Summary;

class PrometheusMiddleware
{
    private $registry;
    private $activeRequests;
    private $responseSizes;
    private $totalRequests;

    public function __construct()
    {
        $this->registry = new CollectorRegistry(new InMemory());
        $this->activeRequests = $this->registry->getOrRegisterGauge(
            'app',
            'active_requests',
            'The number of active requests'
        );
        $this->responseSizes = $this->registry->getOrRegisterSummary(
            'app',
            'response_size',
            'The size of the responses',
            ['method', 'route', 'status_code']
        );
        $this->totalRequests = $this->registry->getOrRegisterCounter(
            'app',
            'total_requests',
            'The total number of requests received'
        );
    }

    public function handle(HttpRequest | HttpClientRequest $request, Closure $next)
    {
        $this->activeRequests->inc();

        $start = microtime(true);

        try {
            // Proceed with the next middleware
            $response = $next($request);

            // Calculate the elapsed time
            $elapsed = microtime(true) - $start;

            // Collect metrics for every route
            $histogram = $this->registry->getOrRegisterHistogram(
                'app',
                'response_time',
                'The time it takes for a request to be processed',
                ['method', 'route', 'status_code'],
                [0.1, 0.5, 1, 2, 5]
            );
            $histogram->observe($elapsed, [
                $request->getMethod(),
                $request->getPathInfo(),
                $response->getStatusCode()
            ]);

            $counter = $this->registry->getOrRegisterCounter(
                'app',
                'request_count',
                'The number of requests',
                ['method', 'route', 'status_code']
            );
            $counter->inc([
                $request->getMethod(),
                $request->getPathInfo(),
                $response->getStatusCode()
            ]);

            $this->totalRequests->inc();

            // Collect response size
            $size = strlen($response->getContent());
            $this->responseSizes->observe($size, [
                $request->getMethod(),
                $request->getPathInfo(),
                $response->getStatusCode()
            ]);

            // Collect memory usage
            $memoryUsage = $this->registry->getOrRegisterGauge(
                'app',
                'memory_usage',
                'Memory usage in bytes'
            );
            $memoryUsage->set(memory_get_usage());

            // Increment error counter for error responses
            if ($response->getStatusCode() >= 400) {
                $errorCounter = $this->registry->getOrRegisterCounter(
                    'app',
                    'error_responses',
                    'The number of error responses',
                    ['method', 'route', 'status_code']
                );
                $errorCounter->inc([
                    $request->getMethod(),
                    $request->getPathInfo(),
                    $response->getStatusCode()
                ]);
            }
        } finally {
            $this->activeRequests->dec();
        }

        // Only return metrics if the /metrics route is hit
        if ($request->getPathInfo() === '/metrics') {
            $renderer = new RenderTextFormat();
            $result = $renderer->render($this->registry->getMetricFamilySamples());
            return response($result, 200, ['Content-Type' => RenderTextFormat::MIME_TYPE]);
        }

        // For all other routes, just return the response
        return $response;
    }
}

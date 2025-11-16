<?php

namespace App;

use App\Contracts\AiClient;
use App\Contracts\AnalysisResultStore;
use App\Contracts\ProcurementFetcher;
use App\Http\Controllers\Api\AnalyzeController as ApiAnalyzeController;
use App\Http\Controllers\AnalyzeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResultController;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Repositories\SQLiteAnalysisResultStore;
use App\Services\AnalysisService;
use App\Services\CacheService;
use App\Services\DeepSeekService;
use App\Services\ProcurementParser;
use App\Support\DatabaseFactory;
use App\Support\SessionStore;
use InvalidArgumentException;
use PDO;

class Application
{
    public AnalysisService $analysisService;

    public function __construct(
        protected Router $router,
        protected SessionStore $session,
        AnalysisService $analysisService
    ) {
        $this->analysisService = $analysisService;
    }

    public static function create(array $overrides = []): self
    {
        $session = $overrides['session'] ?? new SessionStore();
        $cache = $overrides['cache'] ?? new CacheService();
        $procurementFetcher = $overrides['procurementFetcher'] ?? new ProcurementParser();
        $aiClient = $overrides['aiClient'] ?? DeepSeekService::fromEnvironment();
        $resultStore = $overrides['resultStore'] ?? self::createDefaultStore();

        if (! $procurementFetcher instanceof ProcurementFetcher) {
            throw new InvalidArgumentException('procurementFetcher must implement ProcurementFetcher');
        }

        if (! $aiClient instanceof AiClient) {
            throw new InvalidArgumentException('aiClient must implement AiClient');
        }

        if (! $resultStore instanceof AnalysisResultStore) {
            throw new InvalidArgumentException('resultStore must implement AnalysisResultStore');
        }

        $analysisService = $overrides['analysisService'] ?? new AnalysisService(
            $procurementFetcher,
            $aiClient,
            $cache,
            $resultStore
        );

        $router = new Router();

        $homeController = $overrides['homeController'] ?? new HomeController();
        $analyzeController = $overrides['analyzeController'] ?? new AnalyzeController($analysisService);
        $resultController = $overrides['resultController'] ?? new ResultController($analysisService);
        $apiController = $overrides['apiAnalyzeController'] ?? new ApiAnalyzeController($analysisService);

        $router->get('/', [$homeController, '__invoke']);
        $router->post('/analyze', [$analyzeController, '__invoke']);
        $router->get('/result/{id}', [$resultController, 'show']);
        $router->post('/api/analyze', [$apiController, '__invoke']);

        return new self($router, $session, $analysisService);
    }

    public function handle(Request $request): Response
    {
        $request->setSession($this->session);

        $response = $this->router->dispatch($request);

        foreach ($response->flash() as $key => $value) {
            $this->session->flash($key, $value);
        }

        return $response;
    }

    public function session(): SessionStore
    {
        return $this->session;
    }

    public function analysis(): AnalysisService
    {
        return $this->analysisService;
    }

    protected static function createDefaultStore(): AnalysisResultStore
    {
        $pdo = self::createPdo();

        return new SQLiteAnalysisResultStore($pdo);
    }

    protected static function createPdo(): PDO
    {
        return DatabaseFactory::make();
    }
}

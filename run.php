<?php

require_once __DIR__.'\Services\GitHubPullRequestService.php';
require_once __DIR__.'\Services\GitHubRepositoryService';

set_time_limit(0);

switch ($argv[1]) {
    case 'r':
        (new GitHubRepositoryService())->run();
        break;
    case 'p':
        startPrSearch();
        break;
}

function startPrSearch(){
    $service = new GitHubPullRequestService();

    for ($i=0; $i < 100; $i++) {
        $service->run();
        sleep(10);
    }
}
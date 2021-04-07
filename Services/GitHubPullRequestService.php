<?php

require_once __DIR__ . '\GitHubService.php';
require_once __DIR__ . '\..\DbManager.php';

const SEARCH_LIMIT = 100;

class GitHubPullRequestService
{

    public function run()
    {
        $repository = DbManager::getFirstPending();
        $owner = explode('/', $repository["name_with_owner"])[0];
        $name = explode('/', $repository["name_with_owner"])[1];
        $query = $this->getQuery($name, $owner);
        $cursor = 'null';

        do {
            $cursor = $this->searchAndSave($cursor,$query,$repository['id_repositorio']);
        }
        while ($cursor != null);
    }

    private function searchAndSave($cursor,$query,$idRepositorio){
        $GitHubService = new GitHubService();
        $nextCursor = $GitHubService->runQuery($cursor, $query);
        $results = $GitHubService->resultsLine;

        foreach ($results as $line) {
            if($line["state"] == 'OPEN' || $line["reviews"] == 0){
                continue;
            }

            $endDate = $line["state"] == 'MERGED' ? $line["mergedAt"] : $line["closedAt"];
            if(abs(strtotime($endDate) - strtotime($line["createdAt"])) < 60 * 60){ // 1h
                continue;
            }

            DbManager::insertPr($line,$idRepositorio,$endDate);
        }

        return $nextCursor;
    }

    private function getQuery($name, $owner)
    {
        $query = <<<EOD
        {
          repository(owner: "$owner", name: "$name") {
            pullRequests(first: 100) {
              nodes {
                closedAt
                createdAt
                deletions
                additions
                comments {
                  totalCount
                }
                mergedAt
                state
                reviews {
                  totalCount
                }
                body
                assignees {
                  totalCount
                }
                files {
                  totalCount
                }
              }
              pageInfo {
                hasNextPage
                endCursor
              }
            }
          }
          rateLimit {
            cost
            limit
            remaining
          }
      }
        
EOD;
        return $query;
    }
}

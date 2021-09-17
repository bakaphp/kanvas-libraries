<?php

declare(strict_types=1);

namespace Kanvas\Packages\WorkflowsRules\Actions;

use Kanvas\Packages\WorkflowsRules\Actions;
use Kanvas\Packages\WorkflowsRules\Contracts\WorkflowsEntityInterfaces;
use Throwable;

class Leads extends Actions
{
    /**
     * handle.
     *
     * @param WorkflowsEntityInterfaces $entity
     * @param array $params
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(WorkflowsEntityInterfaces $entity) : void
    {
        $args = $entity->getRulesRelatedEntities();
        try {
            $data = $this->getModelsInArray(...$args);
            $referrals = $entity->getMessage()['data']['referrals'];
            $results = [];
            foreach ($referrals as $referral) {
                $peopleClass = get_class($data['peoples']);
                $people = new $peopleClass;
                $peopleData = [
                    'leads' => [
                        [
                            'users_id' => $data['leads']->users_id,
                            'leads_owner_id' => $data['leads']->users_id,
                            'companies_id' => $data['leads']->companies_id,
                            'leads_status_id' => 1,
                            'companies_branches_id' => $data['leads']->companies_branches_id,
                            'leads_receivers_id' => $data['leads']->leads_receivers_id
                        ]
                    ],
                    'users_id' => $data['leads']->users_id,
                    'companies_id' => $data['leads']->companies_id,
                    'name' => $referral['name'] . ' ' . $referral['lastname']
                ];
                $people->saveOrFail($peopleData);
                $people->saveEmail($referral['email']);
                $people->savePhone($referral['telephone']);
                $results[] = $people->toArray();
            }
            $this->setResults($results);
            $this->setStatus(Actions::SUCCESSFUL);
        } catch (Throwable $e) {
            $this->setError($e->getMessage());
            $this->setStatus(Actions::FAIL);
        }
    }
}

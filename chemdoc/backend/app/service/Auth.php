<?php
namespace app\service;

use think\facade\Db;

class Auth
{
    protected array $authConfig = [];

    public function __construct()
    {
        $this->authConfig = [
            'auth_on' => 1,
            'auth_type' => 1,
            'auth_group' => 'auth_group',
            'auth_rule' => 'auth_rule',
            'auth_user' => 'admin_user',
        ];
    }

    public function check(string $name, int $uid = 0): bool
    {
        if (!$this->authConfig['auth_on']) {
            return true;
        }

        $uid = $uid ?: (request()->user_id ?? 0);
        if (empty($uid)) {
            return false;
        }

        if ($uid == 1) {
            return true;
        }

        $ruleName = $this->getRuleName($name);
        $groups = $this->getGroups($uid);

        if (empty($groups)) {
            return false;
        }

        $rules = [];
        foreach ($groups as $group) {
            $ruleIds = trim($group['rules'], ',');
            if (!empty($ruleIds)) {
                $rules = array_merge($rules, explode(',', $ruleIds));
            }
        }

        $rules = array_unique(array_filter($rules));

        if (in_array('*', $rules)) {
            return true;
        }

        $ruleNames = Db::name($this->authConfig['auth_rule'])
            ->whereIn('id', $rules)
            ->column('name');

        return in_array($ruleName, $ruleNames);
    }

    public function getGroups(int $uid): array
    {
        return Db::name($this->authConfig['auth_group_access'])
            ->alias('aga')
            ->join($this->authConfig['auth_group'] . ' ag', 'aga.group_id = ag.id')
            ->where('aga.uid', $uid)
            ->where('ag.status', 1)
            ->select()
            ->toArray();
    }

    public function getUserRules(int $uid): array
    {
        $groups = $this->getGroups($uid);
        
        if (empty($groups)) {
            return [];
        }

        $rules = [];
        foreach ($groups as $group) {
            $ruleIds = trim($group['rules'], ',');
            if (!empty($ruleIds)) {
                $rules = array_merge($rules, explode(',', $ruleIds));
            }
        }

        $rules = array_unique(array_filter($rules));

        if (in_array('*', $rules)) {
            return Db::name($this->authConfig['auth_rule'])
                ->where('status', 1)
                ->column('name');
        }

        return Db::name($this->authConfig['auth_rule'])
            ->whereIn('id', $rules)
            ->where('status', 1)
            ->column('name');
    }

    protected function getRuleName(string $name): string
    {
        if (strpos($name, '/') !== false) {
            return $name;
        }
        
        $request = request();
        $prefix = $request->module() ?: 'api';
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        
        return $prefix . '/' . $controller . '/' . $action;
    }
}

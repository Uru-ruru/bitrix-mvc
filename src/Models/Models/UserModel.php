<?php

namespace Uru\BitrixModels\Models;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Uru\BitrixModels\Queries\UserQuery;

/**
 * UserQuery methods.
 *
 * @method static static getByLogin(string $login)
 * @method static static getByEmail(string $email)
 *
 * Base Query methods
 * @method static Collection|static[]  getList()
 * @method static static               first()
 * @method static static               getById(int $id)
 * @method static UserQuery            sort(string|array $by, string $order = 'ASC')
 * @method static UserQuery            order(string|array $by, string $order = 'ASC') // same as sort()
 * @method static UserQuery            filter(array $filter)
 * @method static UserQuery            addFilter(array $filters)
 * @method static UserQuery            resetFilter()
 * @method static UserQuery            navigation(array $filter)
 * @method static UserQuery            select($value)
 * @method static UserQuery            keyBy(string $value)
 * @method static UserQuery            limit(int $value)
 * @method static UserQuery            offset(int $value)
 * @method static UserQuery            page(int $num)
 * @method static UserQuery            take(int $value) // same as limit()
 * @method static UserQuery            forPage(int $page, int $perPage = 15)
 * @method static LengthAwarePaginator paginate(int $perPage = 15, string $pageName = 'page')
 * @method static Paginator            simplePaginate(int $perPage = 15, string $pageName = 'page')
 * @method static UserQuery            stopQuery()
 * @method static UserQuery            cache(float|int $minutes)
 *
 * Scopes
 * @method static UserQuery active()
 * @method        UserQuery fromGroup(int $groupId)
 */
class UserModel extends BitrixModel
{
    /**
     * Bitrix entity object.
     *
     * @var object
     */
    public static $bxObject;

    /**
     * Corresponding object class name.
     */
    protected static string $objectClass = 'CUser';

    /**
     * Current user cache.
     *
     * @var static
     */
    protected static $currentUser;

    /**
     * Have groups been already fetched from DB?
     */
    protected bool $groupsAreFetched = false;

    /**
     * Instantiate a query object for the model.
     */
    public static function query(): UserQuery
    {
        return new UserQuery(static::instantiateObject(), get_called_class());
    }

    /**
     * Get a new instance for the current user.
     *
     * @return static
     */
    public static function current()
    {
        return is_null(static::$currentUser)
            ? static::freshCurrent()
            : static::$currentUser;
    }

    /**
     * Get a fresh instance for the current user and save it to local cache.
     *
     * @return static
     */
    public static function freshCurrent()
    {
        global $USER;

        return static::$currentUser = (new static($USER->getId()))->load();
    }

    /**
     * Fill model groups if they are already known.
     * Saves DB queries.
     */
    public function fillGroups(array $groups)
    {
        $this->fields['GROUP_ID'] = $groups;

        $this->groupsAreFetched = true;
    }

    /**
     * Load model fields from database if they are not loaded yet.
     *
     * @return $this
     */
    public function load(): BaseBitrixModel
    {
        $this->getFields();
        $this->getGroups();

        return $this;
    }

    /**
     * Get user groups from cache or database.
     */
    public function getGroups(): array
    {
        if ($this->groupsAreFetched) {
            return $this->fields['GROUP_ID'];
        }

        return $this->refreshGroups();
    }

    /**
     * Refresh model from database and place data to $this->fields.
     */
    public function refresh(): array
    {
        $this->refreshFields();

        $this->refreshGroups();

        return $this->fields;
    }

    /**
     * Refresh user fields and save them to a class field.
     */
    public function refreshFields(): mixed
    {
        if (null === $this->id || '0' === $this->id) {
            $this->original = [];

            return $this->fields = [];
        }

        $groupBackup = $this->fields['GROUP_ID'] ?? null;

        $this->fields = static::query()->getById($this->id)->fields;

        if ($groupBackup) {
            $this->fields['GROUP_ID'] = $groupBackup;
        }

        $this->fieldsAreFetched = true;

        $this->original = $this->fields;

        return $this->fields;
    }

    /**
     * Refresh user groups and save them to a class field.
     */
    public function refreshGroups(): array
    {
        if (null === $this->id) {
            return [];
        }

        global $USER;

        $this->fields['GROUP_ID'] = $this->isCurrent()
            ? $USER->getUserGroupArray()
            : static::$bxObject->getUserGroup($this->id);

        $this->groupsAreFetched = true;

        return $this->fields['GROUP_ID'];
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasGroupWithId(1);
    }

    /**
     * Check if this user is the operating user.
     */
    public function isCurrent(): bool
    {
        global $USER;

        return $USER->getId() && $this->id == $USER->getId();
    }

    /**
     * Check if user has role with a given ID.
     *
     * @param mixed $role_id
     */
    public function hasGroupWithId($role_id): bool
    {
        return in_array($role_id, $this->getGroups());
    }

    /**
     * Check if user is authorized.
     */
    public function isAuthorized(): bool
    {
        global $USER;

        return ($USER->getId() == $this->id) && $USER->isAuthorized();
    }

    /**
     * Check if user is guest.
     */
    public function isGuest(): bool
    {
        return !$this->isAuthorized();
    }

    /**
     * Logout user.
     */
    public function logout()
    {
        global $USER;

        $USER->logout();
    }

    /**
     * Scope to get only users from a given group / groups.
     *
     * @param array|int $id
     */
    public function scopeFromGroup(UserQuery $query, $id): UserQuery
    {
        $query->filter['GROUPS_ID'] = $id;

        return $query;
    }

    /**
     * Substitute old group with the new one.
     */
    public function substituteGroup(int $old, int $new)
    {
        $groups = $this->getGroups();

        if (($key = array_search($old, $groups)) !== false) {
            unset($groups[$key]);
        }

        if (!in_array($new, $groups)) {
            $groups[] = $new;
        }

        $this->fields['GROUP_ID'] = $groups;
    }

    /**
     * Fill extra fields when $this->field is called.
     */
    protected function afterFill()
    {
        if (isset($this->fields['GROUP_ID']) && is_array(['GROUP_ID'])) {
            $this->groupsAreFetched = true;
        }
    }
}

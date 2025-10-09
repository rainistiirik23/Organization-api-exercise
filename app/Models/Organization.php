<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organization extends Model
{
    public function parents(): BelongsToMany
    {
        return $this->BelongsToMany(Organization::class, 'organization_relationships', 'daughter_id', 'parent_id');
    }
    public function daughters(): BelongsToMany
    {
        return $this->BelongsToMany(Organization::class, 'oganization_relationships', 'parent_id', 'daughter_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organization extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['name'];
    public function parents(): BelongsToMany
    {
        return $this->BelongsToMany(Organization::class, 'organization_relationships', 'daughter_id', 'parent_id');
    }
    public function daughters(): BelongsToMany
    {
        return $this->BelongsToMany(Organization::class, 'organization_relationships', 'parent_id', 'daughter_id');
    }
}

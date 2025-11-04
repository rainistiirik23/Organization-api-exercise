<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationRelationship;
use Illuminate\Database\Seeder;

class OrganizationRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $OrganizationRelationshipCreationCount = 10000;
        $insertedOrganizationRelationships = [];
        for ($i = 0; $i <= $OrganizationRelationshipCreationCount; $i++) {
            $parentId   = Organization::inRandomOrder()->first()->id;
            $daughterId = Organization::inRandomOrder()->first()->id;
            if ($daughterId == $parentId) {
                continue;
            }
            $insertedOrganizationRelationshipsCount = count($insertedOrganizationRelationships);
            for ($j = 0; $j < $insertedOrganizationRelationshipsCount; $j++) {
                if ($insertedOrganizationRelationships[$j]['parent_id'] == $parentId and $insertedOrganizationRelationships[$j]['daughter_id'] == $daughterId) {
                    continue 2;
                }
            }
            $insertedOrganizationRelationships[] = ['parent_id' => $parentId, 'daughter_id' => $daughterId];
            OrganizationRelationship::create([
                'parent_id'   => $parentId,
                'daughter_id' => $daughterId,
            ]);
        }
    }
}

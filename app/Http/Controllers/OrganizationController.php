<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationRelationship;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{

    public function create(Request $request)
    {
        $organizationName = $request->org_name;
        $doesOrganizationExist = Organization::where('name', $organizationName)->exists();
        if ($doesOrganizationExist) {
            return response()->json(['responseMessage' => "Organization {$organizationName} already exists", "code" => 400], 400);
        }
        Organization::create(['name' => $organizationName]);
        $parentOrganization = Organization::where('name', $organizationName)->get();
        $parentId = $parentOrganization[0]->id;
        $daughterOrganizations = $request->daughters;
        $this->insertDaughterParentOrganizations($daughterOrganizations, $parentId);
        return response()->json(['responseMessage' => 'Organizations and daughter organizations have successfully been added', 'code' => 200], 200);
    }
    public function insertDaughterParentOrganizations($organizations, $parentOrganizationId)
    {
        $daughterOrganizations = [];
        for ($i = 0; $i < count($organizations); $i++) {
            Organization::firstOrCreate(['name' => $organizations[$i]['org_name']]);
            $daughterOrganizations[] = ['name' => $organizations[$i]['org_name']];
        }
        $organizationsWithIds = Organization::whereIn('name', $daughterOrganizations)->get();
        $parentIdwithDaughterIdArray = [];
        for ($i = 0; $i < count($organizationsWithIds); $i++) {
            $parentIdwithDaughterIdArray[] = ['parent_id' => $parentOrganizationId, 'daughter_id' => $organizationsWithIds[$i]['id']];
        }
        OrganizationRelationship::insert($parentIdwithDaughterIdArray);
        for ($i = 0; $i < count($organizations); $i++) {
            if (array_key_exists('daughters', $organizations[$i])) {
                $parentOrganization = Organization::where('name', $organizations[$i]['org_name'])->get();
                $this->insertDaughterParentOrganizations($organizations[$i]['daughters'], $parentOrganization[0]->id);
            }
        }
    }
}

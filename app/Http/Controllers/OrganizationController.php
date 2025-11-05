<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationRelationship;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{

    public function create(Request $request)
    {
        $organizationName = $request->org_name;
        $doesOrganizationExist = Organization::where('name', $organizationName)->exists();
        $isOrganizationStringValueEmpty =  Str::of($organizationName)->trim()->isEmpty();

        if ($isOrganizationStringValueEmpty) {
            return response()->json(['responseMessage' => "Organization value cannot be empty", "code" => 400], 400);
        } elseif ($doesOrganizationExist) {
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
            if (array_key_exists('daughters', $organizations[$i]) && count($organizations[$i]['daughters'])) {
                $parentOrganization = Organization::where('name', $organizations[$i]['org_name'])->get();
                $this->insertDaughterParentOrganizations($organizations[$i]['daughters'], $parentOrganization[0]->id);
            }
        }
    }
    public function show(Request $request)
    {
        $requestedOrganizationName = $request->query('org-name');
        $OrganizationFromDatabase = Organization::where('name', $requestedOrganizationName);
        if (! $OrganizationFromDatabase->exists()) {
            return response()->json(['responseMessage' => "Organization '{$requestedOrganizationName}' does not exist", "code" => 400], 400);
        }
        $organizationId = Organization::where('name', $requestedOrganizationName)->get('id')[0]->id;

        $parentOrganizations = Organization::find($organizationId)->parents()->get();

        $parentOrganizationsIdArray = [];
        foreach ($parentOrganizations as $parentOrganization) {
            $parentOrganizationsIdArray[] = $parentOrganization->id;
        }
        $sisterOrganizationIdValues = OrganizationRelationship::whereIn('parent_id', $parentOrganizationsIdArray)->get('daughter_id');
        $sisterOrganizations = Organization::whereIn('id', $sisterOrganizationIdValues)->get('name');
        $daughterOrganizations = Organization::find($organizationId)->daughters()->get();
        $parentDaughterSisterOrganizationsWithRelationTypes = [];
        foreach ($daughterOrganizations as $daughterOrganization) {
            $parentDaughterSisterOrganizationsWithRelationTypes[] = ['name' => $daughterOrganization->name, 'relationship_type' => 'daughter'];
        };
        foreach ($sisterOrganizations as $sisterOrganization) {
            $parentDaughterSisterOrganizationsWithRelationTypes[] = ['name' => $sisterOrganization->name, 'relationship_type' => 'sister'];
        };
        foreach ($parentOrganizations as $parentOrganization) {
            $parentDaughterSisterOrganizationsWithRelationTypes[] = ['name' => $parentOrganization->name, 'relationship_type' => 'parent'];
        };
        $sortedParentDaughterSisterOrganizationsWithRelationTypes = collect($parentDaughterSisterOrganizationsWithRelationTypes)->sortBy('name')->values()->paginate(100);
        return response($sortedParentDaughterSisterOrganizationsWithRelationTypes, 200);
    }
}

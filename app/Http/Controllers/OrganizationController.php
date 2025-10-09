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
    public function show(Request $request)
    {
        $requestedOrganizationName = $request->query('org-name');
        $OrganizationFromDatabase = Organization::where('name', $requestedOrganizationName);
        if (! $OrganizationFromDatabase->exists()) {
            return response()->json(['responseMessage' => "Organization '{$requestedOrganizationName}' does not exist", "code" => 400], 400);
        }
        $organizationId = Organization::where('name', $request->org_name)->get('id')[0]->id;

        $parentOrganizations = Organization::find($organizationId)->relations()->get();
        return $parentOrganizations;
        $parentOrganizationsIdArray = [];
        foreach ($parentOrganizations as $parentOrganization) {
            $parentOrganizationsIdArray[] = $parentOrganization->parent_id;
        }

        $sisterOrganizations = OrganizationRelationship::whereIn('parent_id', $parentOrganizationsIdArray)->get('daughter_id');
        $sisterOrganizationsIdArray = [];
        foreach ($sisterOrganizations as $sisterOrganization) {
            $sisterOrganizationsIdArray[] = $sisterOrganization->daughter_id;
        }
        $daughterOrganizations = Organization::find($organizationId)->daughters()->get('daughter_id');
        $daughterOrganizationsIdArray = [];
        foreach ($daughterOrganizations as $daughterOrganization) {
            $daughterOrganizationsIdArray[] = $daughterOrganization->daughter_id;
        }
        $parentDaughterSisterIds = array_merge($daughterOrganizationsIdArray, $sisterOrganizationsIdArray, $parentOrganizationsIdArray);
        $organizationsWithUnknownRelationTypes = Organization::whereIn('id', $parentDaughterSisterIds)->select('id', 'name')->orderBy('name')->paginate(100)->all();
        $parentDaughterSisterOrganizationsWithRelationTypes = [];
        foreach ($organizationsWithUnknownRelationTypes as $organizationWithUnknownRelationType) {

            foreach ($daughterOrganizationsIdArray as $daughterOrganizationId) {
                if ($organizationWithUnknownRelationType->id == $daughterOrganizationId) {
                    $organizationWithDaughterRelationType = $organizationWithUnknownRelationType;
                    $organizationWithDaughterRelationType['relationship_type'] = 'daughter';
                    $parentDaughterSisterOrganizationsWithRelationTypes[] = $organizationWithDaughterRelationType;
                    continue 2;
                }
            }
            foreach ($parentOrganizationsIdArray as $parentOrganizationId) {
                if ($organizationWithUnknownRelationType->id == $parentOrganizationId) {
                    $organizationWithParentRelationType = $organizationWithUnknownRelationType;
                    $organizationWithParentRelationType['relationship_type'] = 'parent';
                    $parentDaughterSisterOrganizationsWithRelationTypes[] = $organizationWithParentRelationType;
                    continue 2;
                }
            }
            foreach ($sisterOrganizationsIdArray as $sisterOrganizationId) {
                if ($organizationWithUnknownRelationType->id == $sisterOrganizationId) {
                    $organizationWithParentRelationType = $organizationWithUnknownRelationType;
                    $organizationWithParentRelationType['relationship_type'] = 'sister';
                    $parentDaughterSisterOrganizationsWithRelationTypes[] = $organizationWithParentRelationType;
                    continue 2;
                }
            }
        }
        return response($parentDaughterSisterOrganizationsWithRelationTypes, 200);
    }
}

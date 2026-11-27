<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Repositories\CompanyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $companies = $this->companyRepository->getCompanies($request);

        return Inertia::render('Company/Index', compact('companies', 'filters'));
    }

    public function create()
    {
        return Inertia::render('Company/Create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'strn' => 'nullable|string|max:255',
            'ntn' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                $this->companyRepository->create($validatedData);
            });

            Inertia::flash('toast', ['type' => 'success', 'message' => __('Company created successfully.')]);
            return to_route('companies');
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }

    public function show(Company $company)
    {
        return redirect()->route('companies.edit', $company->id);
    }

    public function edit(Company $company)
    {
        return Inertia::render('Company/Edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'strn' => 'nullable|string|max:255',
            'ntn' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($company, $validatedData) {
                $this->companyRepository->update($company, $validatedData);
            });

            Inertia::flash('toast', ['type' => 'success', 'message' => __('Company updated successfully.')]);
            return to_route('companies');
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }

    public function destroy(Company $company)
    {
        try {
            DB::beginTransaction();
            $this->companyRepository->delete($company);
            DB::commit();
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Company deleted successfully.')]);
            return to_route('companies');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back();
        }
    }
}

<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyRepository
{
    public $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function getCompanies(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        return $this->company
            ->when($search !== '', function ($query) use ($search) {
                $like = "%{$search}%";

                $query->where(function ($subQuery) use ($like) {
                    $subQuery
                        ->whereLike('name', $like)
                        ->orWhereLike('branch', $like)
                        ->orWhereLike('address', $like)
                        ->orWhereLike('strn', $like)
                        ->orWhereLike('ntn', $like);
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();
    }

    public function create(array $data): Company
    {
        return $this->company->create($data);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company;
    }

    public function delete(Company $company)
    {
      return  $company->delete();
    }
}

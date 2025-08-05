<?php

namespace Zenobio93\Seat\SkillChecker\Http\DataTables;

use Zenobio93\Seat\SkillChecker\Models\SkillList;
use Yajra\DataTables\Services\DataTable;

/**
 * Class SkillListDataTable.
 */
class SkillListDataTable extends DataTable
{
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function ajax(): \Illuminate\Http\JsonResponse
    {
        return datatables()
            ->eloquent($this->applyScopes($this->query()))
            ->editColumn('name', function ($row) {
                return '<strong>' . e($row->name) . '</strong>';
            })
            ->editColumn('description', function ($row) {
                return $row->description ? \Str::limit($row->description, 50) : '';
            })
            ->editColumn('skills_count', function ($row) {
                return '<span class="badge badge-info">' . $row->requirements->count() . '</span>';
            })
            ->editColumn('priority', function ($row) {
                return '<span class="badge badge-secondary">' . $row->priority . '</span>';
            })
            ->editColumn('is_required', function ($row) {
                if ($row->is_required) {
                    return '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> ' . trans('skillchecker::skillchecker.required') . '</span>';
                } else {
                    return '<span class="badge badge-info"><i class="fas fa-info-circle"></i> ' . trans('skillchecker::skillchecker.optional') . '</span>';
                }
            })
            ->editColumn('created_by', function ($row) {
                return $row->creator ? $row->creator->name : 'Unknown';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i');
            })
            ->editColumn('action', function ($row) {
                $actions = '<div class="btn-group" role="group">';
                
                // View button
                $actions .= '<a href="' . route('skillchecker.skill-lists.show', $row) . '" class="btn btn-sm btn-info" title="' . trans('skillchecker::skillchecker.view') . '">';
                $actions .= '<i class="fas fa-eye"></i></a>';
                
                // Edit button (with permission check)
                if (auth()->user()->can('skillchecker.manage_skill_lists')) {
                    $actions .= '<a href="' . route('skillchecker.skill-lists.edit', $row) . '" class="btn btn-sm btn-warning" title="' . trans('skillchecker::skillchecker.edit') . '">';
                    $actions .= '<i class="fas fa-edit"></i></a>';
                    
                    // Delete button
                    $actions .= '<form action="' . route('skillchecker.skill-lists.destroy', $row) . '" method="POST" class="d-inline" onsubmit="return confirm(\'' . trans('skillchecker::skillchecker.are_you_sure_delete') . '\')">';
                    $actions .= csrf_field();
                    $actions .= method_field('DELETE');
                    $actions .= '<button type="submit" class="btn btn-sm btn-danger" title="' . trans('skillchecker::skillchecker.delete') . '">';
                    $actions .= '<i class="fas fa-trash"></i></button></form>';
                }
                
                $actions .= '</div>';
                
                return $actions;
            })
            ->rawColumns(['name', 'skills_count', 'priority', 'is_required', 'action'])
            ->toJson();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
        return SkillList::with('creator')
            ->withCount('requirements')
            ->select('skill_lists.*');
    }

    /**
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): \Yajra\DataTables\Html\Builder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->orderBy(3, 'asc') // Order by priority desc (index 3)
        ;
    }

    /**
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            [
                'data' => 'name',
                'title' => trans('skillchecker::skillchecker.name'),
                'orderable' => true,
                'searchable' => true,
            ],
            [
                'data' => 'description',
                'title' => trans('skillchecker::skillchecker.description'),
                'orderable' => false,
                'searchable' => true,
            ],
            [
                'data' => 'skills_count',
                'title' => trans('skillchecker::skillchecker.skills'),
                'orderable' => true,
                'searchable' => false,
            ],
            [
                'data' => 'priority',
                'title' => trans('skillchecker::skillchecker.priority'),
                'orderable' => true,
                'searchable' => false,
            ],
            [
                'data' => 'is_required',
                'title' => trans('skillchecker::skillchecker.required'),
                'orderable' => true,
                'searchable' => false,
            ],
            [
                'data' => 'created_by',
                'title' => trans('skillchecker::skillchecker.created_by'),
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'created_at',
                'title' => trans('skillchecker::skillchecker.created_at'),
                'orderable' => true,
                'searchable' => false,
            ],
            [
                'data' => 'action',
                'title' => trans('skillchecker::skillchecker.actions'),
                'orderable' => false,
                'searchable' => false,
                'exportable' => false,
                'printable' => false,
            ],
        ];
    }
}
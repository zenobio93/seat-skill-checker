<?php

namespace Zenobio93\Seat\SkillChecker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Seat\Eveapi\Models\Sde\InvType;

class SkillPlanRequirement extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skill_plan_requirements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'skill_plan_id',
        'skill_id',
        'required_level',
        'priority',
        'is_required',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'skill_plan_id' => 'integer',
        'skill_id' => 'integer',
        'required_level' => 'integer',
        'priority' => 'integer',
        'is_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the skill plan this requirement belongs to.
     *
     * @return BelongsTo
     */
    public function skillPlan(): BelongsTo
    {
        return $this->belongsTo(SkillPlan::class);
    }

    /**
     * Get the EVE skill type information.
     *
     * @return BelongsTo
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(InvType::class, 'skill_id', 'typeID');
    }

    /**
     * Scope to filter by skill plan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $skillPlanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSkillPlan($query, int $skillPlanId)
    {
        return $query->where('skill_plan_id', $skillPlanId);
    }

    /**
     * Scope to filter by skill ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $skillId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSkill($query, int $skillId)
    {
        return $query->where('skill_id', $skillId);
    }

    /**
     * Get the skill name from the related InvType.
     *
     * @return string
     */
    public function getSkillNameAttribute(): string
    {
        return $this->skill->typeName ?? 'Unknown Skill';
    }

    /**
     * Scope to order by priority.
     * Priority 1 is the highest priority, 2 is second highest, etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority')->orderBy('id');
    }

    /**
     * Scope to filter by required status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $required
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequired($query, bool $required = true)
    {
        return $query->where('is_required', $required);
    }
}
<?php

namespace App\Services\Stp;

use App\Enums\StpApproachStatus;
use App\Models\StpApproach;
use App\Models\StpTeam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class StpBoardService
{
    public function moveApproach(int $approachId, int $sessionId, ?int $toTeamId, int $toIndex, ?int $fromTeamId): void
    {
        DB::transaction(function () use ($approachId, $sessionId, $toTeamId, $toIndex, $fromTeamId): void {
            $approach = StpApproach::query()->lockForUpdate()->findOrFail($approachId);

            if ((int) $approach->stp_session_id !== $sessionId) {
                throw new InvalidArgumentException('A abordagem não pertence à sessão informada.');
            }

            if ($toTeamId !== null) {
                $teamExistsInSession = StpTeam::query()
                    ->where('id', $toTeamId)
                    ->where('stp_session_id', $sessionId)
                    ->exists();

                if (! $teamExistsInSession) {
                    throw new InvalidArgumentException('A equipe de destino não pertence à sessão informada.');
                }
            }

            Gate::authorize('update', $approach);

            $originTeamId = $approach->stp_team_id;
            $this->applyMoveAndStatus($approach, $toTeamId);

            $effectiveFromTeamId = $fromTeamId ?? $originTeamId;
            $this->normalizePositions($sessionId, $effectiveFromTeamId);
            $this->insertAtIndex($approach->id, $sessionId, $toTeamId, $toIndex);

            if ($effectiveFromTeamId !== $toTeamId) {
                $this->normalizePositions($sessionId, $toTeamId);
            }
        });
    }

    public function normalizePositions(int $sessionId, ?int $teamId): void
    {
        $query = StpApproach::query()
            ->where('stp_session_id', $sessionId)
            ->orderBy('position')
            ->orderBy('id');

        if ($teamId === null) {
            $query->whereNull('stp_team_id');
        } else {
            $query->where('stp_team_id', $teamId);
        }

        $approaches = $query->get(['id', 'position']);

        foreach ($approaches as $position => $approach) {
            if ((int) $approach->position === $position) {
                continue;
            }

            StpApproach::query()
                ->where('id', $approach->id)
                ->update(['position' => $position]);
        }
    }

    private function applyMoveAndStatus(StpApproach $approach, ?int $toTeamId): void
    {
        $nextStatus = $approach->status;

        if ($toTeamId !== null && $approach->status === StpApproachStatus::Planned) {
            $nextStatus = StpApproachStatus::Assigned;
        }

        if (
            $toTeamId === null
            && $approach->status === StpApproachStatus::Assigned
        ) {
            $nextStatus = StpApproachStatus::Planned;
        }

        $approach->stp_team_id = $toTeamId;
        $approach->status = $nextStatus;
        $approach->save();
    }

    private function insertAtIndex(int $approachId, int $sessionId, ?int $teamId, int $toIndex): void
    {
        $query = StpApproach::query()
            ->where('stp_session_id', $sessionId)
            ->where('id', '!=', $approachId)
            ->orderBy('position')
            ->orderBy('id');

        if ($teamId === null) {
            $query->whereNull('stp_team_id');
        } else {
            $query->where('stp_team_id', $teamId);
        }

        $ids = $query->pluck('id')->all();
        $targetIndex = max(0, min($toIndex, count($ids)));
        array_splice($ids, $targetIndex, 0, [$approachId]);

        foreach ($ids as $position => $id) {
            StpApproach::query()
                ->where('id', $id)
                ->update(['position' => $position]);
        }
    }
}

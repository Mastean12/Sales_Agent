<?php

namespace App\Enums;

enum AuditAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case StageTransitioned = 'stage_transitioned';
    case OwnerReassigned = 'owner_reassigned';
}

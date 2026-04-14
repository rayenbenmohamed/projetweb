<?php

namespace App\Entity;

enum OfferStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';
    case DELETED = 'DELETED';
}

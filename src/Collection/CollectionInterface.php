<?php
namespace App\Collection;

use ArrayAccess;
use Countable;
use Iterator;
use Serializable;
use Stringable;

interface CollectionInterface extends Iterator, Countable, ArrayAccess, Serializable, Stringable {}

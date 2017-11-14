<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RecommendGoodsUpdate extends Event
{
    use SerializesModels;

    public $areaId;
    public $recommendGoodsId;

    /**
     * Create a new event instance.
     * RecommendGoodsUpdate constructor.
     *
     * @param int $areaId           大区ID
     * @param int $recommendGoodsId 推荐商品记录ID
     */
    public function __construct($areaId = 0, $recommendGoodsId = 0)
    {
        $this->areaId = $areaId;
        $this->recommendGoodsId = $recommendGoodsId;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}

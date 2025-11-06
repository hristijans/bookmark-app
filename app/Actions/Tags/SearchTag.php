<?php

namespace App\Actions\Tags;

use App\Models\Link;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

class SearchTag
{
    public function execute(array $data = []): ?Collection
    {
        $linksCountSub = DB::table('taggables')
            ->join('links', 'links.id', '=', 'taggables.taggable_id')
            ->whereColumn('taggables.tag_id', 'tags.id')
            ->where('taggables.taggable_type', Link::class)
            ->whereNull('links.deleted_at')
            ->when(auth()->check(), function ($query) {
                $query->where('links.user_id', auth()->id());
            })
            ->selectRaw('count(*)');

        return Tag::query()
            ->orderBy('name')
            ->select(['id', 'name', 'slug'])
            ->selectSub($linksCountSub, 'links_count')
            ->get();
    }
}

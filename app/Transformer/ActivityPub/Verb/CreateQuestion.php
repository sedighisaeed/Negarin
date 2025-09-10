<?php

namespace App\Transformer\ActivityPub\Verb;

use App\Status;
use League\Fractal;
use Illuminate\Support\Str;

class CreateQuestion extends Fractal\TransformerAbstract
{
	protected $defaultIncludes = [
        'object',
    ];

	public function transform(Status $status)
	{
		return [
			'@context' => [
				'https://w3id.org/security/v1',
				'https://www.w3.org/ns/activitystreams',
				[
					'Hashtag' 			=> 'as:Hashtag',
					'sensitive' 		=> 'as:sensitive',
					'schema' 			=> 'http://schema.org/',
					'negarin' 			=> 'http://negarin.org/ns#',
					'commentsEnabled' 	=> [
						'@id' 			=> 'negarin:commentsEnabled',
						'@type' 		=> 'schema:Boolean'
					],
					'capabilities'		=> [
						'@id' 			=> 'negarin:capabilities',
						'@container' 	=> '@set'
					],
					'announce'			=> [
						'@id' 			=> 'negarin:canAnnounce',
						'@type' 		=> '@id'
					],
					'like'				=> [
						'@id' 			=> 'negarin:canLike',
						'@type' 		=> '@id'
					],
					'reply'				=> [
						'@id' 			=> 'negarin:canReply',
						'@type' 		=> '@id'
					],
					'toot' 				=> 'http://joinmastodon.org/ns#',
					'Emoji'				=> 'toot:Emoji'
				]
			],
			'id' 					=> $status->permalink(),
			'type' 					=> 'Create',
			'actor' 				=> $status->profile->permalink(),
			'published' 			=> $status->created_at->toAtomString(),
			'to' 					=> $status->scopeToAudience('to'),
			'cc' 					=> $status->scopeToAudience('cc'),
		];
	}

	public function includeObject(Status $status)
	{
		return $this->item($status, new Question());
	}
}














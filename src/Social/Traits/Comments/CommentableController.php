<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Traits\Comments;

use Baka\Contracts\Http\Api\CrudBehaviorRelationshipsTrait;
use Baka\Validation as CanvasValidation;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Kanvas\Packages\Social\Dto\Comments as CommentsDto;
use Kanvas\Packages\Social\Mappers\Comments as CommentsMapper;
use Kanvas\Packages\Social\Models\MessageComments;
use Kanvas\Packages\Social\Models\Messages;
use Kanvas\Packages\Social\Models\Users;
use Kanvas\Packages\Social\Services\Comments;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Channels Trait.
 */
trait CommentableController
{
    use ProcessOutputMapperTrait {
        processOutput as public mapperProcessOutput;
    }
    use CrudBehaviorRelationshipsTrait{
        processOutput as public crudProcessOutput;
    }

    /**
     *  Lead variable.
     */
    protected Messages $message;

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new MessageComments();
        $this->dto = CommentsDto::class;
        $this->dtoMapper = new CommentsMapper();

        $this->parentId = (int) $this->router->getParams()['messageId'];

        $this->message = Messages::findFirstOrFail([
            'conditions' => 'id = :messages_id: 
                            AND apps_id = :apps_id:
                            AND companies_id = :companies_id:
                            AND is_deleted = 0',
            'bind' => [
                'messages_id' => $this->parentId,
                'apps_id' => $this->app->getId(),
                'companies_id' => $this->userData->getDefaultCompany()->getId(),
            ]
        ]);

        if (!$this->parentId) {
            throw new RuntimeException('Not Found');
        }

        $this->model->message_id = $this->parentId;
        $this->model->companies_id = $this->userData->getDefaultCompany()->getId();
        $this->model->apps_id = $this->app->getId();
        $this->additionalSearchFields = [
            ['message_id', ':', $this->parentId],
            ['apps_id', ':', $this->app->getId()],
            ['companies_id', ':', $this->userData->getDefaultCompany()->getId()],
            ['is_deleted', ':', 0],
        ];
    }

    /**
     * Format Controller Result base on a Mapper.
     *
     * @param mixed $results
     *
     * @return void
     */
    protected function processOutput($results)
    {
        return  $this->mapperProcessOutput($results);
    }

    /**
     * Get comment by its id.
     *
     * @param int $commentId
     *
     * @return Response
     */
    public function getComment(int $commentId) : Response
    {
        return $this->response(Comments::getById((string)$commentId));
    }

    /**
     * processInput function.
     *
     * @param array $request
     *
     * @return array
     */
    protected function processInput(array $request) : array
    {
        $validation = new CanvasValidation();
        $validation->add('message', new PresenceOf(['message' => _('message is required.')]));
        $validation->validate($request);

        $request['users_id'] = $request['users_id'] ?? $this->userData->getId();

        return $request;
    }

    /**
     * Add a new comment to a message.
     *
     * @param int $messageId
     *
     * @return Response
     */
    public function addComment(int $messageId) : Response
    {
        $request = $this->processInput($this->request->getPostData());

        // We need to validate that the users_id exists too. This validation only exists when creating comments
        $validation = new CanvasValidation();
        $validation->add('users_id', new PresenceOf(['message' => _('users_id is required.')]));
        $validation->validate($request);

        //Fetch the user and throw exception when not found
        $user = Users::findFirstOrFail($request['users_id']);
        $newComment = Comments::add((string)$messageId, $request['message'], $user);

        return $this->response($this->processOutput($newComment));
    }

    /**
     * Add a new comment to a message.
     *
     * @param int $messageId
     *
     * @return Response
     */
    public function editComment(int $messageId, int $commentId) : Response
    {
        $request = $this->processInput($this->request->getPutData());
        $newComment = Comments::edit((string)$commentId, $request['message']);

        return $this->response($this->processOutput($newComment));
    }

    /**
     * Add a new comment to a message.
     *
     * @param int $commentId
     *
     * @return Response
     */
    public function deleteComment(int $messageId, int $commentId) : Response
    {
        return $this->response(Comments::delete((string)$commentId, $this->userData));
    }
}

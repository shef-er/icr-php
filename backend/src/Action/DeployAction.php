<?php
declare(strict_types=1);

namespace App\Action;

use App\Action;
use Psr\Http\Message\ResponseInterface as Response;

class DeployAction extends Action
{
    protected function action(): Response
    {
        $target = $this->resolveArg('target');

        $is_token = (
            $this->request->hasHeader('X-Gitlab-Token')
            && $this->request->getHeaderLine('X-Gitlab-Token') == DEPLOY_TOKEN
        );

        if(
            $target == 'backend'
            && $is_token
        ) {
            return $this->backDeploy();
        } 
        elseif(
            $target == 'frontend'
            && $is_token
        ) {
            return $this->frontDeploy();
        }

        return $this->response->withStatus(405);
    }

    protected function backDeploy(): Response
    {
        // $cmd = '
        //     bash /var/www/svarta/www/deploy.sh \
        //         target="'.$target.'" \
        //         credentials="'.DEPLOY_CREDENTIALS.'" \
        // ';
        $cmd = '
            git pull '. DEPLOY_CREDENTIALS.' 2>&1 | tee deploy-log.txt
        ';
        $cwd = getcwd();
        chdir(DEPLOY_DIR);
        $data = shell_exec($cmd);
        chdir($cwd);
        
        return $this->respondWithData(is_array($data) ? $data[0] : $data );
    }

    protected function frontDeploy(): Response
    {
        // $cmd = '
        //     bash /var/www/svarta/www/deploy.sh \
        //         target="'.$target.'" \
        //         credentials="'.DEPLOY_FRONTEND_CREDENTIALS.'" \
        // ';
        $cmd = '
            git pull '. DEPLOY_FRONTEND_CREDENTIALS.' 2>&1 | tee deploy-log.txt
        ';
        $cwd = getcwd();
        chdir(DEPLOY_FRONTEND_DIR);
        $data = shell_exec($cmd);
        chdir($cwd);
        
        return $this->respondWithData(is_array($data) ? $data[0] : $data );
    }
}

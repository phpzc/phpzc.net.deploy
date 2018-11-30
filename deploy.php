<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'phpzc.net');
set('application_path','/home/www/{{application}}');
set('shared_path','{{application_path}}/shared');

// Project repository
set('repository', 'https://github.com/phpzc/phpzc.net.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', ['.env']);
add('shared_dirs', ['public/Public']);

// Writable dirs by web server
add('writable_dirs', []);

// 顺便把 composer 的 vendor 目录也加进来  加快下载速度 复制上个版本代码里面的目录
//add('copy_dirs', ['node_modules', 'vendor']); //不复制 node_modules
add('copy_dirs', [ 'vendor']);
// Hosts

host('115.29.35.86')
    ->user('root')
    ->identityFile('~/.ssh/id_rsa')
    ->set('deploy_path', '{{application_path}}');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

desc('Upload .env file');
task('env:upload',function(){

    upload('.env','{{shared_path}}/.env');
});

after('deploy:shared','env:upload');


// 定义一个前端编译的任务
desc('Yarn');
task('deploy:yarn', function () {
    // release_path 是 Deployer 的一个内部变量，代表当前代码目录路径
    // run() 的默认超时时间是 5 分钟，而 yarn 相关的操作又比较费时，因此我们在第二个参数传入 timeout = 600，指定这个命令的超时时间是 10 分钟
    run('cd {{release_path}} && SASS_BINARY_SITE=http://npm.taobao.org/mirrors/node-sass yarn && yarn production', ['timeout' => 6000]);
});

task('artisan:config:cache', function () {
    //不做配置缓存
});

after('artisan:config:cache','artisan:storage:link');


// 定义一个后置钩子，当 deploy:failed 任务被执行之后，Deployer 会执行 deploy:unlock 任务
after('deploy:failed', 'deploy:unlock');

// 定义一个前置钩子，在执行 deploy:symlink 任务之前先执行 artisan:migrate
before('deploy:symlink', 'artisan:migrate');

// 在 deploy:vendors 之前调用 deploy:copy_dirs
before('deploy:vendors', 'deploy:copy_dirs');

// 定义一个后置钩子，在 deploy:vendors 之后执行 deploy:yarn 任务
// 不执行这个yarn 本项目没使用
//after('deploy:vendors', 'deploy:yarn');


//添加路由缓存  Deployer 的 laravel 部署脚本内已经内置了 artisan:route:cache 这个任务，只不过是没有放在 deploy 任务组中，所以我们只需要添加一个后置钩子即可：
// route里面 有闭包 不可缓存
//after('artisan:config:cache', 'artisan:route:cache');



//desc('git:phpzc.net.Public');
//task('deploy:git:phpzc.net.Public', function () {
    //更新phpzc.net.Public
    // run() 的默认超时时间是 5 分钟
    //run('cd {{shared_path}}/public/Public && git pull', ['timeout' => 600]);
//});
//after('deploy:symlink', 'deploy:git:phpzc.net.Public');

server "66.175.216.43", :web, :app, :db, :primary => true

set :application, "sandbox"
set :database, application
set :user, "deployer"
set :group, "www-data"
set :deploy_to, "/home/#{user}/apps/#{application}"
set :deploy_via, :remote_cache
set :use_sudo, false
set :retain_installer, true

set :scm, "git"
set :repository_name, "lemonstand-sandbox"
set :repository, "git@github.com:DYE/#{fetch(:repository_name, application)}.git"
set :branch, "deployer"

default_run_options[:pty] = true
ssh_options[:forward_agent] = true

after "deploy", "deploy:cleanup" # keep only the last 5 releases

namespace :deploy do

  def remove_installer?
    !retain_installer
  end

  ## SETUP TASKS ##
  desc "Create the database"
  task :create_database, :roles => :db do
    run "mysql -u root -p"
    run "create database #{application};"
    run "grant all on sandbox.* to root@localhost identified by 'root';"
    run "exit"
    logger.info "~> Database successfully created"
  end
  before "deploy:setup", "deploy:create_database"

  desc "Create the shared logs directory and provide write access to the user"
  task :setup_logs, :roles => :app do
    run "mkdir -p #{shared_path}/logs && chmod u+w #{shared_path}/logs"
    run "touch #{shared_path}/logs/errors.txt"
  end
  after "deploy:setup", "deploy:setup_logs"

  desc "Create the backups directory and provide write access to the user"
  task :setup_backup_dir, :roles => :app do
    run "mkdir -p #{deploy_to}/backups && chmod 775 #{deploy_to}/backups"
  end
  after "deploy:setup", "deploy:setup_backup_dir"

  desc "Create the shared configs folder and provide executable access to the group"
  task :setup_configs, :roles => :app do
    run "mkdir -p #{shared_path}/config && chmod g+x #{shared_path}/config"
  end
  after "deploy:setup", "deploy:setup_configs"

  desc "Create the shared uploaded folder and provide write access to the group"
  task :setup_uploaded, :roles => :app do
    run "mkdir -p #{shared_path}/uploaded && chmod g+w #{shared_path}/uploaded"
  end
  after "deploy:setup", "deploy:setup_uploaded"

  ## DEPLOY TASKS ##
  desc "Symlink the config.php to the shared path"
  task :symlink_config, :roles => :app do
    run "ln -nfs #{shared_path}/config/config.php #{release_path}/config/config.php"
  end
  after "deploy", "deploy:symlink_config"

  desc "Make sure local git is in sync with remote."
  task :check_revision, :roles => :web do
    unless `git rev-parse HEAD` == `git rev-parse origin/#{branch}`
      puts "WARNING: HEAD is not the same as origin/#{branch}"
      puts "Run `git push` to sync changes."
      exit
    end
  end
  before "deploy", "deploy:check_revision"

  desc "Symlink the logs"
  task :symlink_logs, :roles => :app do 
    run "rm -rf #{current_path}/logs && ln -s #{shared_path}/logs #{current_path}/logs"
  end
  after "deploy", "deploy:symlink_logs"

  desc "Symlink the uploads"
  task :symlink_uploads, :roles => :app do 
    run "rm -rf #{current_path}/uploaded && ln -s #{shared_path}/uploaded #{current_path}/uploaded"
  end
  after "deploy", "deploy:symlink_uploads"

  desc "Remove install scripts if applicable"
  task :destroy_installer, :roles => :app do 
    run "#{sudo} rm -rf #{current_path}/installer_files"
    run "#{sudo} rm -f  #{current_path}/install.php"
  end
  after "deploy", "deploy:destroy_installer" if remove_installer?

  desc "Remove local config.dat"
  task :destroy_config, :roles => :app do 
    run "#{sudo} rm -f #{current_path}/config/config.dat"
  end
  after "deploy", "deploy:destroy_config"

  desc "Set ownership to #{user}:#{group}"
  task :set_ownership, :roles => :app do 
    run "#{sudo} chown -R #{user}:#{group} #{deploy_to}"
  end
  before "deploy:setup", "deploy:set_ownership"
  after "deploy", "deploy:set_ownership"
  after "deploy:setup", "deploy:set_ownership"

  desc "Move config.dat to shared folder"
  task :move_secure_config, :roles => :app do 
    run "#{sudo} mv #{deploy_to}/current/config/config.dat #{shared_path}/config/config.dat"
  end

  task :restart, :except => { :no_release => true } do
    run "#{sudo} service nginx reload"
  end
end
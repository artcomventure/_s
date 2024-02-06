# mysql
alias importDB="mysql -u root -ppassword PROJECT_NAME < /var/www/html/docker/db.sql"

# wordpress
alias stage2dev="wp search-replace 'STAGE_URL' 'DEV_URL' --allow-root"
alias live2dev="wp search-replace 'LIVE_URL' 'DEV_URL' --allow-root"

# WP CLI commands to update WP and else:
# For more info see https://developer.wordpress.org/cli/commands/
# ---
# Update WP: `wp core update --allow-root`
# Update DB: `wp core update-db --allow-root`
# Update WP language packs: `wp language core update --allow-root`
# Update plugins: `wp plugin update --all --allow-root`
# Update plugin language packs: `wp language plugin update --all --allow-root`
# Update themes: `wp theme update --all --allow-root`
# Update theme language packs: `wp language theme update --all --allow-root`
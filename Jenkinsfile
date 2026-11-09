pipeline {
    agent any

    options {
        timestamps()
        timeout(time: 30, unit: 'MINUTES')
    }

    environment {
        COMPOSER_ALLOW_SUPERUSER = '1'
        COMPOSER_NO_INTERACTION  = '1'
        APP_ENV                  = 'testing'
        XDEBUG_MODE              = 'coverage'
    }

    stages {
        stage('Checkout') {
            steps { checkout scm }
        }

        stage('Dependencies (Composer)') {
            steps {
                // Image CI harus memiliki PHP >= 8.3 dengan ext-gd, ext-zip, ext-mbstring, dan Xdebug/PCOV.
                sh 'composer install --no-progress --prefer-dist'
                sh 'cp -n .env.example .env || true'
                sh 'php artisan key:generate --force'
            }
        }

        stage('Security Audit') {
            steps {
                // composer audit mengembalikan exit code != 0 jika ada kerentanan -> pipeline gagal.
                sh 'composer audit --no-interaction'
            }
        }

        stage('Frontend Build') {
            steps {
                sh 'npm ci --ignore-scripts'
                sh 'npm run build'
            }
        }

        stage('Test & Coverage') {
            steps {
                sh '''
                    vendor/bin/phpunit \
                        --coverage-text=coverage.txt \
                        --coverage-clover=coverage.xml \
                        --log-junit=junit.xml
                    cat coverage.txt
                '''
            }
            post {
                always { junit allowEmptyResults: true, testResults: 'junit.xml' }
            }
        }

        stage('Quality Gate: Coverage 100%') {
            steps {
                sh '''
                    php -r '
                        $x = simplexml_load_file("coverage.xml");
                        $m = $x->project->metrics;
                        $st = (int) $m["statements"]; $cs = (int) $m["coveredstatements"];
                        $me = (int) $m["methods"];    $cm = (int) $m["coveredmethods"];
                        printf("Statements: %d/%d, Methods: %d/%d\\n", $cs, $st, $cm, $me);
                        if ($cs < $st || $cm < $me) { fwrite(STDERR, "Coverage kurang dari 100%\\n"); exit(1); }
                    '
                '''
            }
        }

        stage('Trivy Scan') {
            steps {
                sh 'trivy fs --exit-code 1 --severity HIGH,CRITICAL --skip-dirs vendor,node_modules --scanners vuln,secret .'
            }
        }

        stage('Package') {
            steps {
                // Artefak tanpa vendor/ dan node_modules/; .env.example tetap ikut, .env tidak.
                sh 'zip -r latest-app.zip . -x "vendor/*" "node_modules/*" ".git/*" ".env" "coverage*" "junit.xml" ".phpunit.cache/*"'
                archiveArtifacts artifacts: 'latest-app.zip', fingerprint: true
            }
        }
    }

    post {
        always { cleanWs() }
    }
}

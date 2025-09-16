<?php
/**
 * Plugin Name:       AI Post Corrector
 * Plugin URI:        https://github.com/andrejamesson/ai-post-corrector
 * Description:       Usa IA para corrigir a gramática e o estilo dos posts do WordPress.
 * Version:           1.3
 * Author:            André Jamesson
 * Text Domain:       ai-post-corrector
 * GitHub Plugin URI: andrejamesson/ai-post-corrector
 * Domain Path:       /languages
 */

// Impede o acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constantes do plugin
define( 'AIC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AIC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'AIC_VERSION', '1.3' );

/**
 * Classe principal do plugin
 */
class AI_Post_Corrector {
    
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
        // Inicializar GitHub Updater
        $this->init_github_updater();
    }

    /**
     * Inicializar GitHub Updater
     */
    private function init_github_updater() {
        if ( is_admin() ) {
            new AIC_GitHub_Updater( __FILE__, 'andrejamesson', 'ai-post-corrector' );
        }
    }
    
    public function init() {
        // Hooks para o admin
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        
        // Hook para AJAX
        add_action( 'wp_ajax_aic_correct_post_content', array( $this, 'correct_post_content_handler' ) );
        add_action( 'wp_ajax_aic_generate_title', array( $this, 'generate_title_handler' ) );
    }
    
    /**
     * Adiciona menu de configurações
     */
    public function add_admin_menu() {
        add_options_page(
            'AI Post Corrector Settings',
            'AI Corrector',
            'manage_options',
            'ai_corrector',
            array( $this, 'options_page_html' )
        );
    }
    
    /**
     * Registra configurações
     */
    public function settings_init() {
        register_setting( 'aic_settings_group', 'aic_api_key' );
        register_setting( 'aic_settings_group', 'aic_api_provider' );
        
        add_settings_section(
            'aic_settings_section',
            'Configurações da API de IA',
            null,
            'ai_corrector'
        );
        
        add_settings_field(
            'aic_api_provider_field',
            'Provedor de IA',
            array( $this, 'api_provider_field_html' ),
            'ai_corrector',
            'aic_settings_section'
        );
        
        add_settings_field(
            'aic_api_key_field',
            'Chave da API',
            array( $this, 'api_key_field_html' ),
            'ai_corrector',
            'aic_settings_section'
        );
    }
    
    /**
     * HTML para o campo do provedor de API
     */
    public function api_provider_field_html() {
        $provider = get_option( 'aic_api_provider', 'openai' );
        ?>
        <select name="aic_api_provider">
            <option value="openai" <?php selected( $provider, 'openai' ); ?>>OpenAI (GPT-3.5/GPT-4)</option>
            <option value="gemini" <?php selected( $provider, 'gemini' ); ?>>Google Gemini</option>
        </select>
        <p class="description">Escolha o provedor de IA que deseja usar.</p>
        <?php
    }
    
    /**
     * HTML para o campo da chave de API
     */
    public function api_key_field_html() {
        $api_key = get_option( 'aic_api_key' );
        echo '<input type="password" name="aic_api_key" value="' . esc_attr( $api_key ) . '" size="50" />';
        echo '<p class="description">Insira sua chave de API do provedor selecionado.</p>';
    }
    
    /**
     * HTML para a página de configurações
     */
    public function options_page_html() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'aic_settings_group' );
                do_settings_sections( 'ai_corrector' );
                submit_button( 'Salvar Configurações' );
                ?>
            </form>
            
            <div class="aic-info-box" style="margin-top: 20px; padding: 15px; background: #f1f1f1; border-left: 4px solid #0073aa;">
                <h3>Como obter sua chave de API:</h3>
                <ul>
                    <li><strong>OpenAI:</strong> Acesse <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com/api-keys</a></li>
                    <li><strong>Google Gemini:</strong> Acesse <a href="https://makersuite.google.com/app/apikey" target="_blank">makersuite.google.com/app/apikey</a></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Adiciona meta box ao editor de posts
     */
    public function add_meta_box() {
        $post_types = array( 'post', 'page' );
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'aic_corrector_metabox',
                'Corretor com Inteligência Artificial',
                array( $this, 'meta_box_html' ),
                $post_type,
                'side',
                'high'
            );
        }
    }
    
    /**
     * HTML do meta box
     */
    public function meta_box_html( $post ) {
        ?>
        <div class="aic-metabox">
            <p>Use os botões abaixo para melhorar seu texto com IA:</p>
            
            <div class="aic-buttons">
                <button type="button" class="button button-primary aic-action-btn" data-action="generate_title">
                    <span class="dashicons dashicons-admin-post"></span> Gerar Título
                </button>
                
                <button type="button" class="button button-primary aic-action-btn" data-action="correct">
                    <span class="dashicons dashicons-edit"></span> Corrigir Gramática
                </button>
                
                <button type="button" class="button aic-action-btn" data-action="improve">
                    <span class="dashicons dashicons-star-filled"></span> Melhorar Estilo
                </button>
                
                <button type="button" class="button aic-action-btn" data-action="summarize">
                    <span class="dashicons dashicons-format-aside"></span> Resumir
                </button>
            </div>
            
            <div class="aic-status-area">
                <span id="aic-spinner" class="spinner" style="float: none; visibility: hidden;"></span>
                <p id="aic-status" style="color: #666; margin: 10px 0 0 0;"></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Carrega assets (CSS e JS)
     */
    public function enqueue_assets( $hook ) {
        // Carregar apenas nas telas de edição de post
        if ( 'post.php' != $hook && 'post-new.php' != $hook ) {
            return;
        }
        
        wp_enqueue_script(
            'aic-corrector-js',
            AIC_PLUGIN_URL . 'js/corrector.js',
            array( 'jquery', 'wp-blocks', 'wp-element', 'wp-data' ),
            AIC_VERSION,
            true
        );
        
        wp_enqueue_style(
            'aic-corrector-css',
            AIC_PLUGIN_URL . 'css/corrector.css',
            array(),
            AIC_VERSION
        );
        
        // Passar dados para o JavaScript
        wp_localize_script( 'aic-corrector-js', 'aic_ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'aic_nonce' )
        ) );
    }
    
    /**
     * Handler AJAX para gerar título
     */
    public function generate_title_handler() {
        // Verificar nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'aic_nonce' ) ) {
            wp_die( 'Erro de segurança' );
        }

        // Verificar permissões
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( 'Permissões insuficientes' );
        }

        $content = sanitize_textarea_field( $_POST['content'] );
        
        if ( empty( $content ) ) {
            wp_send_json_error( 'Conteúdo vazio. Escreva algum texto antes de gerar o título.' );
        }

        $api_provider = get_option( 'aic_api_provider', 'openai' );
        
        try {
            if ( $api_provider === 'openai' ) {
                $result = $this->generate_title_openai( $content );
            } else {
                $result = $this->generate_title_gemini( $content );
            }
            
            wp_send_json_success( $result );
        } catch ( Exception $e ) {
            wp_send_json_error( 'Erro ao gerar título: ' . $e->getMessage() );
        }
    }

    /**
     * Gerar título usando OpenAI
     */
    private function generate_title_openai( $content ) {
        $api_key = get_option( 'aic_api_key' );
        
        if ( empty( $api_key ) ) {
            throw new Exception( 'Chave da API OpenAI não configurada. Vá em Configurações > AI Post Corrector para configurar.' );
        }

        $prompt = "Com base no seguinte conteúdo, gere um título atrativo, claro e otimizado para SEO. O título deve ter entre 40-60 caracteres e capturar a essência do texto:\n\n" . $content;

        $data = array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'Você é um especialista em criação de títulos para conteúdo web. Crie títulos atrativos, claros e otimizados para SEO.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 100,
            'temperature' => 0.7
        );

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode( $data ),
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            throw new Exception( 'Erro na requisição: ' . $response->get_error_message() );
        }

        $body = wp_remote_retrieve_body( $response );
        $result = json_decode( $body, true );

        if ( isset( $result['error'] ) ) {
            throw new Exception( $result['error']['message'] );
        }

        if ( ! isset( $result['choices'][0]['message']['content'] ) ) {
            throw new Exception( 'Resposta inválida da API' );
        }

        return trim( $result['choices'][0]['message']['content'] );
    }

    /**
     * Gerar título usando Google Gemini
     */
    private function generate_title_gemini( $content ) {
        $api_key = get_option( 'aic_api_key' );
        
        if ( empty( $api_key ) ) {
            throw new Exception( 'Chave da API Google Gemini não configurada. Vá em Configurações > AI Post Corrector para configurar.' );
        }

        $prompt = "Com base no seguinte conteúdo, gere um título atrativo, claro e otimizado para SEO. O título deve ter entre 40-60 caracteres e capturar a essência do texto:\n\n" . $content;

        $data = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $prompt
                        )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 100,
            ),
            'safetySettings' => array(
                array(
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                )
            )
        );

        $response = wp_remote_post( 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . $api_key, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode( $data ),
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            throw new Exception( 'Erro na requisição: ' . $response->get_error_message() );
        }

        $body = wp_remote_retrieve_body( $response );
        $result = json_decode( $body, true );

        if ( isset( $result['error'] ) ) {
            throw new Exception( $result['error']['message'] );
        }

        if ( ! isset( $result['candidates'][0]['content']['parts'][0]['text'] ) ) {
            throw new Exception( 'Resposta inválida da API' );
        }

        return trim( $result['candidates'][0]['content']['parts'][0]['text'] );
    }
    public function correct_post_content_handler() {
        // Verificar nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'aic_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Erro de segurança.' ) );
            return;
        }
        
        // Obter dados
        $content = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';
        $action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : 'correct';
        
        if ( empty( $content ) ) {
            wp_send_json_error( array( 'message' => 'Nenhum conteúdo para processar.' ) );
            return;
        }
        
        // Obter configurações
        $api_key = get_option( 'aic_api_key' );
        $provider = get_option( 'aic_api_provider', 'openai' );
        
        if ( empty( $api_key ) ) {
            wp_send_json_error( array( 'message' => 'Chave de API não configurada. Acesse Configurações > AI Corrector.' ) );
            return;
        }
        
        // Processar com base no provedor
        if ( $provider === 'openai' ) {
            $result = $this->process_with_openai( $content, $action_type, $api_key );
        } elseif ( $provider === 'gemini' ) {
            $result = $this->process_with_gemini( $content, $action_type, $api_key );
        } else {
            wp_send_json_error( array( 'message' => 'Provedor de IA não suportado.' ) );
            return;
        }
        
        if ( $result['success'] ) {
            wp_send_json_success( array( 'corrected_content' => $result['content'] ) );
        } else {
            wp_send_json_error( array( 'message' => $result['message'] ) );
        }
    }
    
    /**
     * Processa conteúdo com OpenAI
     */
    private function process_with_openai( $content, $action_type, $api_key ) {
        $prompts = array(
            'correct' => "Você é um assistente de escrita especialista em português do Brasil. Corrija a gramática, ortografia, pontuação e melhore o estilo do texto a seguir. Mantenha o tom original. Não adicione introduções ou conclusões, apenas retorne o texto corrigido:\n\n",
            'improve' => "Você é um assistente de escrita especialista. Melhore o estilo, clareza e fluidez do texto a seguir, mantendo o significado original. Torne-o mais envolvente e profissional:\n\n",
            'summarize' => "Você é um assistente especialista em resumos. Crie um resumo conciso e informativo do texto a seguir, mantendo os pontos principais:\n\n"
        );
        
        $prompt = $prompts[$action_type] . $content;
        
        $api_url = 'https://api.openai.com/v1/chat/completions';
        
        $body = array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array( 'role' => 'user', 'content' => $prompt )
            ),
            'temperature' => 0.5,
            'max_tokens' => 2048,
        );
        
        $args = array(
            'method'    => 'POST',
            'headers'   => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'body'      => json_encode( $body ),
            'timeout'   => 60,
        );
        
        $response = wp_remote_post( $api_url, $args );
        
        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'message' => 'Erro ao conectar-se à API da OpenAI.' );
        }
        
        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $response_body['choices'][0]['message']['content'] ) ) {
            return array( 'success' => true, 'content' => trim( $response_body['choices'][0]['message']['content'] ) );
        } else {
            $error_message = isset( $response_body['error']['message'] ) ? $response_body['error']['message'] : 'Resposta inválida da API.';
            return array( 'success' => false, 'message' => $error_message );
        }
    }
    
    /**
     * Processa conteúdo com Google Gemini
     */
    private function process_with_gemini( $content, $action_type, $api_key ) {
        $prompts = array(
            'correct' => "Corrija a gramática, ortografia e pontuação do seguinte texto em português do Brasil. Mantenha o tom original. Retorne apenas o texto corrigido:\n\n",
            'improve' => "Melhore o estilo e clareza do seguinte texto, mantendo o significado original. Retorne apenas o texto melhorado:\n\n",
            'summarize' => "Crie um resumo conciso do seguinte texto. Retorne apenas o resumo:\n\n"
        );
        
        $prompt = $prompts[$action_type] . $content;
        
        // Usar a versão v1 da API com o modelo gemini-1.5-flash
        $api_url = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . $api_key;
        
        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array( 'text' => $prompt )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.5,
                'maxOutputTokens' => 2048,
                'topP' => 0.8,
                'topK' => 40
            ),
            'safetySettings' => array(
                array(
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                )
            )
        );
        
        $args = array(
            'method'    => 'POST',
            'headers'   => array(
                'Content-Type' => 'application/json',
            ),
            'body'      => json_encode( $body ),
            'timeout'   => 60,
        );
        
        $response = wp_remote_post( $api_url, $args );
        
        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'message' => 'Erro ao conectar-se à API do Gemini.' );
        }
        
        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        // Verificar se há erro na resposta
        if ( isset( $response_body['error'] ) ) {
            $error_message = $response_body['error']['message'];
            return array( 'success' => false, 'message' => 'Erro da API Gemini: ' . $error_message );
        }
        
        // Verificar se a resposta foi bloqueada por segurança
        if ( isset( $response_body['candidates'][0]['finishReason'] ) && 
             $response_body['candidates'][0]['finishReason'] === 'SAFETY' ) {
            return array( 'success' => false, 'message' => 'Conteúdo bloqueado pelos filtros de segurança do Gemini.' );
        }
        
        // Extrair o texto da resposta
        if ( isset( $response_body['candidates'][0]['content']['parts'][0]['text'] ) ) {
            $result_text = trim( $response_body['candidates'][0]['content']['parts'][0]['text'] );
            return array( 'success' => true, 'content' => $result_text );
        } else {
            return array( 'success' => false, 'message' => 'Resposta inválida da API Gemini.' );
        }
    }
}

/**
 * Classe GitHub Updater para atualizações automáticas
 */
class AIC_GitHub_Updater {
    
    private $file;
    private $plugin;
    private $basename;
    private $active;
    private $username;
    private $repository;
    private $authorize_token;
    private $github_response;

    public function __construct( $file, $username, $repository, $authorize_token = '' ) {
        $this->file = $file;
        $this->plugin = get_plugin_data( $this->file );
        $this->basename = plugin_basename( $this->file );
        $this->active = is_plugin_active( $this->basename );
        $this->username = $username;
        $this->repository = $repository;
        $this->authorize_token = $authorize_token;

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
        add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
        
        // Adicionar link de configurações
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
    }

    /**
     * Obter informações do repositório GitHub
     */
    private function get_repository_info() {
        if ( is_null( $this->github_response ) ) {
            $request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository );
            
            if ( ! empty( $this->authorize_token ) ) {
                $request_uri = add_query_arg( 'access_token', $this->authorize_token, $request_uri );
            }

            $response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_uri ) ), true );

            if ( is_array( $response ) ) {
                $response = current( $response );
            }

            $this->github_response = $response;
        }
    }

    /**
     * Modificar transient de atualizações
     */
    public function modify_transient( $transient ) {
        if ( property_exists( $transient, 'checked' ) ) {
            if ( $checked = $transient->checked ) {
                $this->get_repository_info();

                $out_of_date = version_compare( $this->github_response['tag_name'], $checked[ $this->basename ], 'gt' );

                if ( $out_of_date ) {
                    $new_files = $this->github_response['zipball_url'];

                    $slug = current( explode( '/', $this->basename ) );

                    $plugin = array(
                        'url' => $this->plugin["PluginURI"],
                        'slug' => $slug,
                        'package' => $new_files,
                        'new_version' => $this->github_response['tag_name']
                    );

                    $transient->response[ $this->basename ] = (object) $plugin;
                }
            }
        }

        return $transient;
    }

    /**
     * Plugin popup para mostrar detalhes da atualização
     */
    public function plugin_popup( $result, $action, $args ) {
        if ( ! empty( $args->slug ) ) {
            if ( $args->slug == current( explode( '/', $this->basename ) ) ) {
                $this->get_repository_info();

                $plugin = array(
                    'name' => $this->plugin["Name"],
                    'slug' => $this->basename,
                    'requires' => '5.0',
                    'tested' => '6.4',
                    'rating' => '100.0',
                    'num_ratings' => '10823',
                    'downloaded' => '14249',
                    'added' => '2024-01-01',
                    'version' => $this->github_response['tag_name'],
                    'author' => $this->plugin["AuthorName"],
                    'author_profile' => $this->plugin["AuthorURI"],
                    'last_updated' => $this->github_response['published_at'],
                    'homepage' => $this->plugin["PluginURI"],
                    'short_description' => $this->plugin["Description"],
                    'sections' => array(
                        'Description' => $this->plugin["Description"],
                        'Updates' => $this->github_response['body'],
                    ),
                    'download_link' => $this->github_response['zipball_url']
                );

                return (object) $plugin;
            }
        }
        return $result;
    }

    /**
     * Após instalação
     */
    public function after_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;

        $install_directory = plugin_dir_path( $this->file );
        $wp_filesystem->move( $result['destination'], $install_directory );
        $result['destination'] = $install_directory;

        if ( $this->active ) {
            activate_plugin( $this->basename );
        }

        return $result;
    }

    /**
     * Adicionar meta links na página de plugins
     */
    public function plugin_row_meta( $links, $file ) {
        if ( $file == $this->basename ) {
            $links[] = '<a href="https://github.com/' . $this->username . '/' . $this->repository . '" target="_blank">GitHub</a>';
            $links[] = '<a href="https://github.com/' . $this->username . '/' . $this->repository . '/releases" target="_blank">Releases</a>';
        }
        return $links;
    }
}

// Inicializar o plugin
new AI_Post_Corrector();
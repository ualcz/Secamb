(() => {
    const nomeBancoLegado = 'secamb-requerimento-rascunhos';

    function objetoSelecionado(form) {
        return form.querySelector('input[name="objetoDoRequerimento"]:checked')?.value || '';
    }

    function urlComObjeto(urlBase, objeto) {
        const url = new URL(urlBase, window.location.href);
        if (objeto) url.searchParams.set('objeto', objeto);
        return url.toString();
    }

    function urlRascunho(form, objeto) {
        return urlComObjeto(form.dataset.draftUrl, objeto);
    }

    function cabecalhos(form, json = false) {
        const token = form.querySelector('input[name="_token"]')?.value;
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (token) headers['X-CSRF-TOKEN'] = token;
        if (json) headers['Content-Type'] = 'application/json';

        return headers;
    }

    function mensagemErro(data, padrao) {
        const erros = data?.errors ? Object.values(data.errors).flat() : [];
        return erros.join(' ') || data?.message || padrao;
    }

    async function requisicaoJson(form, url, opcoes, mensagemPadrao) {
        const { json, headers = {}, ...fetchOptions } = opcoes;
        const resposta = await fetch(url, {
            credentials: 'same-origin',
            ...fetchOptions,
            headers: {
                ...cabecalhos(form, json),
                ...headers,
            },
        });
        const dados = await resposta.json().catch(() => null);

        if (resposta.redirected || !resposta.ok || !dados) {
            throw new Error(mensagemErro(dados, resposta.status === 401 || resposta.redirected
                ? 'Sua sessão expirou. Entre novamente antes de continuar.'
                : mensagemPadrao));
        }

        return dados;
    }

    function exibirStatus(form, mensagem, erro = false) {
        const status = document.getElementById('status-rascunho-requerimento');
        if (!status || !form) return;

        status.textContent = mensagem;
        status.classList.toggle('erro', erro);
        status.classList.toggle(
            'tem-rascunho',
            !erro && /^(rascunho|este requerimento tem um rascunho)/i.test(mensagem)
        );
    }

    function marcarObjetoComRascunho(form, objeto) {
        const radio = Array.from(form.querySelectorAll('input[name="objetoDoRequerimento"]'))
            .find(campo => campo.value === objeto);
        const cartao = radio?.closest('.card-objeto-requerimento');
        if (!cartao) return;

        if (!cartao.querySelector('.objeto-rascunho-badge')) {
            const indicador = document.createElement('span');
            indicador.className = 'objeto-rascunho-badge';
            indicador.setAttribute('role', 'status');
            indicador.textContent = 'Este objeto tem um rascunho';
            cartao.querySelector('.card-objeto-header')?.after(indicador);
        }
    }

    function coletarCampos(form, objetoOverride = null) {
        const campos = {};
        Array.from(form.elements).forEach(campo => {
            const salvarDetalheOutroDesabilitado = campo.name === 'objeto_outro' && objetoOverride === 'outro';
            if (!campo.name || campo.name === '_token' || (campo.disabled && !salvarDetalheOutroDesabilitado) ||
                ['file', 'submit', 'button', 'reset', 'password'].includes(campo.type)) return;

            if (campo.type === 'radio') {
                if (campo.checked) campos[campo.name] = campo.value;
            } else if (campo.type === 'checkbox') {
                campos[campo.name] ??= [];
                if (campo.checked) campos[campo.name].push(campo.value);
            } else {
                campos[campo.name] = campo.value;
            }
        });

        const etapaAtiva = Array.from(form.querySelectorAll('.form-step'))
            .find(etapa => window.getComputedStyle(etapa).display !== 'none');
        const objeto = objetoOverride || objetoSelecionado(form);
        if (objeto) campos.objetoDoRequerimento = objeto;

        return {
            campos,
            etapa: etapaAtiva?.dataset.step ?? '1',
            objeto,
        };
    }

    function temConteudoPreenchido(form, dados) {
        const nomesComConteudo = ['empreendimento_id', 'objeto_outro', 'motivo'];
        const camposComConteudo = nomesComConteudo.some(nome => String(dados.campos[nome] || '').trim() !== '');
        const temArquivo = Array.from(form.querySelectorAll('input[type="file"]'))
            .some(input => input.files && input.files.length > 0);

        return camposComConteudo || temArquivo || form.dataset.objetoEscolhidoUsuario === 'true';
    }

    function enfileirar(form, operacao) {
        const anterior = form.__draftSavePromise || Promise.resolve();
        const proxima = anterior.catch(() => {}).then(operacao);
        form.__draftSavePromise = proxima;
        return proxima;
    }

    function salvarCampos(form, objetoOverride = null, ativarSePreenchido = false) {
        if (!form?.dataset.draftUrl || form.dataset.suspendDraft === 'true') {
            return Promise.resolve(false);
        }

        const dados = coletarCampos(form, objetoOverride);
        if (!dados.objeto) return Promise.resolve(false);

        if (form.dataset.draftActive !== 'true' && ativarSePreenchido && temConteudoPreenchido(form, dados)) {
            form.dataset.draftActive = 'true';
        }
        if (form.dataset.draftActive !== 'true') return Promise.resolve(false);

        return enfileirar(form, async () => {
            exibirStatus(form, 'Salvando rascunho online...');
            try {
                await requisicaoJson(
                    form,
                    urlRascunho(form, dados.objeto),
                    { method: 'PUT', body: JSON.stringify(dados), json: true },
                    'Não foi possível salvar o rascunho online.'
                );
                form.dataset.draftObject = dados.objeto;
                marcarObjetoComRascunho(form, dados.objeto);
                exibirStatus(form, '');
                return true;
            } catch (error) {
                console.error('Falha ao salvar o rascunho online:', error);
                exibirStatus(form, error.message || 'Não foi possível salvar o rascunho online.', true);
                return false;
            }
        });
    }

    function salvarArquivos(form, inputAlterado = null) {
        if (!form?.dataset.draftFilesUrl || form.dataset.suspendDraft === 'true') {
            return Promise.resolve();
        }

        const inputs = inputAlterado
            ? [inputAlterado]
            : Array.from(form.querySelectorAll('input[type="file"]'));
        const arquivosPorCampo = inputs.map(input => ({
            campo: input.name,
            arquivos: Array.from(input.files || []),
        }));
        const objeto = form.dataset.draftObject || objetoSelecionado(form);
        const temArquivoSelecionado = arquivosPorCampo.some(item => item.arquivos.length > 0);

        if (!objeto || (form.dataset.draftActive !== 'true' && !temArquivoSelecionado)) {
            return Promise.resolve(false);
        }
        form.dataset.draftActive = 'true';

        return enfileirar(form, async () => {
            try {
                for (const item of arquivosPorCampo) {
                    const dados = new FormData();
                    dados.append('objeto', objeto);
                    dados.append('etapa', '3');
                    dados.append('campo', item.campo);
                    item.arquivos.forEach(arquivo => {
                        dados.append('arquivos[]', arquivo, arquivo.name);
                    });

                    await requisicaoJson(
                        form,
                        urlComObjeto(form.dataset.draftFilesUrl, objeto),
                        { method: 'POST', body: dados },
                        `Não foi possível salvar os arquivos do campo "${item.campo}" online.`
                    );
                }

                form.dataset.draftObject = objeto;
                marcarObjetoComRascunho(form, objeto);
                exibirStatus(form, 'Anexos atualizados.');
                return true;
            } catch (error) {
                console.error('Falha ao salvar os anexos online:', error);
                exibirStatus(form, error.message || 'Não foi possível salvar os anexos online.', true);
                return false;
            }
        });
    }

    function lerRascunhoLegado(chave) {
        if (!window.indexedDB) return Promise.resolve(null);

        return new Promise(resolve => {
            const requisicao = indexedDB.open(nomeBancoLegado, 1);
            requisicao.onupgradeneeded = evento => {
                if (evento.oldVersion === 0) requisicao.transaction.abort();
            };
            requisicao.onerror = () => resolve(null);
            requisicao.onsuccess = () => {
                const banco = requisicao.result;
                if (!banco.objectStoreNames.contains('rascunhos') || !banco.objectStoreNames.contains('arquivos')) {
                    banco.close();
                    resolve(null);
                    return;
                }

                const transacao = banco.transaction(['rascunhos', 'arquivos'], 'readonly');
                const draftRequest = transacao.objectStore('rascunhos').get(chave);
                const faixa = IDBKeyRange.bound([chave, '', 0], [chave, '\uffff', Number.MAX_SAFE_INTEGER]);
                const filesRequest = transacao.objectStore('arquivos').getAll(faixa);
                let rascunho;
                let arquivos;

                draftRequest.onsuccess = () => { rascunho = draftRequest.result; };
                filesRequest.onsuccess = () => { arquivos = filesRequest.result; };
                transacao.oncomplete = () => {
                    banco.close();
                    resolve(rascunho ? { rascunho, arquivos: arquivos || [] } : null);
                };
                transacao.onerror = () => {
                    banco.close();
                    resolve(null);
                };
            };
        });
    }

    function limparRascunhoLegado(chave) {
        return new Promise(resolve => {
            if (!window.indexedDB) return resolve();

            const requisicao = indexedDB.open(nomeBancoLegado, 1);
            requisicao.onupgradeneeded = evento => {
                if (evento.oldVersion === 0) requisicao.transaction.abort();
            };
            requisicao.onerror = () => resolve();
            requisicao.onsuccess = () => {
                const banco = requisicao.result;
                if (!banco.objectStoreNames.contains('rascunhos') || !banco.objectStoreNames.contains('arquivos')) {
                    banco.close();
                    resolve();
                    return;
                }

                const transacao = banco.transaction(['rascunhos', 'arquivos'], 'readwrite');
                transacao.objectStore('rascunhos').delete(chave);
                const faixa = IDBKeyRange.bound([chave, '', 0], [chave, '\uffff', Number.MAX_SAFE_INTEGER]);
                const arquivos = transacao.objectStore('arquivos');
                const arquivosRequest = arquivos.getAll(faixa);
                arquivosRequest.onsuccess = () => {
                    arquivosRequest.result.forEach(arquivo => {
                        arquivos.delete([arquivo.chave, arquivo.campo, arquivo.indice]);
                    });
                };
                transacao.oncomplete = () => {
                    banco.close();
                    resolve();
                };
                transacao.onerror = () => {
                    banco.close();
                    resolve();
                };
            };
        });
    }

    async function migrarRascunhoLegado(form, local, objeto) {
        await requisicaoJson(
            form,
            urlRascunho(form, objeto),
            {
                method: 'PUT',
                body: JSON.stringify({
                    campos: local.rascunho.campos || {},
                    etapa: '3',
                    objeto,
                }),
                json: true,
            },
            'Não foi possível transferir o rascunho antigo para sua conta.'
        );

        const arquivosPorCampo = new Map();
        local.arquivos.forEach(arquivo => {
            if (!arquivosPorCampo.has(arquivo.campo)) arquivosPorCampo.set(arquivo.campo, []);
            arquivosPorCampo.get(arquivo.campo).push(arquivo);
        });

        for (const [campo, arquivos] of arquivosPorCampo) {
            const dados = new FormData();
            dados.append('objeto', objeto);
            dados.append('etapa', '3');
            dados.append('campo', campo);
            arquivos.sort((a, b) => a.indice - b.indice).forEach(arquivo => {
                dados.append('arquivos[]', arquivo.conteudo, arquivo.nome);
            });
            await requisicaoJson(
                form,
                urlComObjeto(form.dataset.draftFilesUrl, objeto),
                { method: 'POST', body: dados },
                'Não foi possível transferir os anexos antigos para sua conta.'
            );
        }

        await limparRascunhoLegado(form.dataset.draftKey);
    }

    async function ler(form, objeto) {
        const dados = await requisicaoJson(
            form,
            urlRascunho(form, objeto),
            { method: 'GET' },
            'Não foi possível recuperar o rascunho da sua conta.'
        );
        if (dados.rascunho) return dados;

        const local = await lerRascunhoLegado(form.dataset.draftKey);
        if (!local) return dados;

        const objetoLegado = local.rascunho.campos?.objetoDoRequerimento;
        if (!objetoLegado || String(local.rascunho.etapa) !== '2') return dados;

        await migrarRascunhoLegado(form, local, objetoLegado);
        marcarObjetoComRascunho(form, objetoLegado);
        if (objetoLegado !== objeto) return dados;

        return requisicaoJson(
            form,
            urlRascunho(form, objeto),
            { method: 'GET' },
            'O rascunho foi transferido, mas não pôde ser recuperado.'
        );
    }

    function restaurarCampos(form, campos) {
        Object.entries(campos || {}).forEach(([nome, valor]) => {
            Array.from(form.elements)
                .filter(campo => campo.name === nome)
                .forEach(campo => {
                    if (campo.type === 'radio') {
                        campo.checked = campo.value === valor;
                        if (campo.checked) campo.dispatchEvent(new Event('change', { bubbles: true }));
                    } else if (campo.type === 'checkbox') {
                        campo.checked = Array.isArray(valor) && valor.includes(campo.value);
                        campo.dispatchEvent(new Event('change', { bubbles: true }));
                    } else if (campo.type !== 'file') {
                        campo.value = valor;
                        campo.dispatchEvent(new Event('input', { bubbles: true }));
                        campo.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
        });
    }

    async function restaurarArquivos(form, arquivos) {
        if (!arquivos.length) return 0;
        if (typeof DataTransfer === 'undefined' || typeof File === 'undefined') {
            throw new Error('Este navegador não permite restaurar os arquivos anexados.');
        }

        const restaurados = await Promise.all(arquivos.map(async arquivo => {
            const resposta = await fetch(arquivo.url, {
                credentials: 'same-origin',
                headers: { Accept: 'application/octet-stream' },
            });
            if (!resposta.ok) {
                throw new Error(`Não foi possível recuperar o arquivo "${arquivo.nome}".`);
            }

            return {
                ...arquivo,
                conteudo: await resposta.blob(),
            };
        }));

        const porCampo = new Map();
        restaurados.forEach(arquivo => {
            if (!porCampo.has(arquivo.campo)) porCampo.set(arquivo.campo, []);
            porCampo.get(arquivo.campo).push(arquivo);
        });

        porCampo.forEach((itens, nome) => {
            const input = Array.from(form.querySelectorAll('input[type="file"]'))
                .find(campo => campo.name === nome);
            if (!input) return;

            const transferencia = new DataTransfer();
            itens.sort((a, b) => a.indice - b.indice).forEach(arquivo => {
                transferencia.items.add(new File(
                    [arquivo.conteudo],
                    arquivo.nome,
                    { type: arquivo.tipo || arquivo.conteudo.type, lastModified: arquivo.ultimaModificacao }
                ));
            });
            input.files = transferencia.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        return restaurados.length;
    }

    async function limpar(form, objeto = null) {
        const objetoParaLimpar = objeto || form?.dataset.draftObject || (form ? objetoSelecionado(form) : '');
        if (!form?.dataset.draftUrl || !objetoParaLimpar) return;

        await Promise.resolve(form.__draftSavePromise).catch(() => {});
        await requisicaoJson(
            form,
            urlRascunho(form, objetoParaLimpar),
            { method: 'DELETE' },
            'Não foi possível excluir o rascunho enviado.'
        );
    }

    async function mudarObjeto(form, novoObjeto) {
        const objetoAtual = form.dataset.draftObject;
        if (form.dataset.draftActive === 'true' && objetoAtual) {
            const salvo = await salvarCampos(form, objetoAtual);
            if (!salvo) {
                const radioAtual = Array.from(form.querySelectorAll('input[name="objetoDoRequerimento"]'))
                    .find(campo => campo.value === objetoAtual);
                if (radioAtual) {
                    radioAtual.checked = true;
                    radioAtual.dispatchEvent(new Event('change', { bubbles: true }));
                }
                return;
            }
        }

        const url = new URL(form.dataset.formUrl, window.location.href);
        url.searchParams.set('objeto', novoObjeto);
        const empreendimento = form.querySelector('[name="empreendimento_id"]')?.value;
        if (empreendimento) url.searchParams.set('empreendimento_id', empreendimento);
        window.location.assign(url.toString());
    }

    window.rascunhoRequerimento = {
        exibirStatus,
        ler,
        limpar,
        mudarObjeto,
        restaurarArquivos,
        restaurarCampos,
        salvarArquivos,
        salvarCampos,
    };
    window.limparRascunhoRequerimento = limpar;
})();

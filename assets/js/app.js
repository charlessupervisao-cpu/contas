(function () {
  var base = (document.body && document.body.getAttribute("data-base")) || "";

  // Olhinho: mostrar / ocultar senha
  document.querySelectorAll("[data-password-toggle]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var wrap = btn.closest(".password-field");
      var input = wrap && wrap.querySelector("input");
      if (!input) return;
      var show = input.type === "password";
      input.type = show ? "text" : "password";
      btn.setAttribute("aria-label", show ? "Ocultar senha" : "Mostrar senha");
      btn.setAttribute("title", show ? "Ocultar senha" : "Mostrar senha");
      btn.classList.toggle("is-visible", show);
      var iconShow = btn.querySelector(".pw-icon-show");
      var iconHide = btn.querySelector(".pw-icon-hide");
      if (iconShow) iconShow.hidden = show;
      if (iconHide) iconHide.hidden = !show;
    });
  });

  // Avisos de operação (gravado/apagado/etc.) somem em até 3s
  document.querySelectorAll("[data-auto-dismiss]").forEach(function (el) {
    var ms = parseInt(el.getAttribute("data-auto-dismiss") || "3000", 10);
    if (!ms || ms < 500) ms = 3000;
    setTimeout(function () {
      el.classList.add("is-dismissing");
      setTimeout(function () {
        if (el.parentNode) el.parentNode.removeChild(el);
      }, 380);
    }, ms);
  });

  function onlyDigits(v) {
    return String(v || "").replace(/\D+/g, "");
  }

  function isValidCpf(value) {
    var d = onlyDigits(value);
    if (d.length !== 11 || /^(\d)\1+$/.test(d)) return false;
    var i, sum, rest;
    sum = 0;
    for (i = 0; i < 9; i++) sum += parseInt(d.charAt(i), 10) * (10 - i);
    rest = (sum * 10) % 11;
    if (rest === 10) rest = 0;
    if (rest !== parseInt(d.charAt(9), 10)) return false;
    sum = 0;
    for (i = 0; i < 10; i++) sum += parseInt(d.charAt(i), 10) * (11 - i);
    rest = (sum * 10) % 11;
    if (rest === 10) rest = 0;
    return rest === parseInt(d.charAt(10), 10);
  }

  function isValidCnpj(value) {
    var d = onlyDigits(value);
    if (d.length !== 14 || /^(\d)\1+$/.test(d)) return false;
    var w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    var w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    var sum = 0;
    var i;
    for (i = 0; i < 12; i++) sum += parseInt(d.charAt(i), 10) * w1[i];
    var r = sum % 11;
    var d1 = r < 2 ? 0 : 11 - r;
    if (d1 !== parseInt(d.charAt(12), 10)) return false;
    sum = 0;
    for (i = 0; i < 13; i++) sum += parseInt(d.charAt(i), 10) * w2[i];
    r = sum % 11;
    var d2 = r < 2 ? 0 : 11 - r;
    return d2 === parseInt(d.charAt(13), 10);
  }

  function maskCpf(v) {
    var d = onlyDigits(v).slice(0, 11);
    return d
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  }

  function maskCnpj(v) {
    var d = onlyDigits(v).slice(0, 14);
    return d
      .replace(/^(\d{2})(\d)/, "$1.$2")
      .replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
      .replace(/\.(\d{3})(\d)/, ".$1/$2")
      .replace(/(\d{4})(\d)/, "$1-$2");
  }

  function maskCpfCnpj(v) {
    var d = onlyDigits(v);
    if (d.length <= 11) return maskCpf(d);
    return maskCnpj(d);
  }

  function maskPhone(v) {
    var d = onlyDigits(v).slice(0, 11);
    if (!d) return "";
    if (d.length <= 2) return "(" + d;
    if (d.length <= 6) return "(" + d.slice(0, 2) + ") " + d.slice(2);
    if (d.length <= 10) {
      return "(" + d.slice(0, 2) + ") " + d.slice(2, 6) + "-" + d.slice(6);
    }
    return "(" + d.slice(0, 2) + ") " + d.slice(2, 7) + "-" + d.slice(7);
  }

  function maskCep(v) {
    var d = onlyDigits(v).slice(0, 8);
    return d.replace(/(\d{5})(\d{1,3})$/, "$1-$2");
  }

  function maskMoney(v) {
    var d = onlyDigits(v);
    if (!d) return "";
    d = d.replace(/^0+(?=\d)/, "");
    while (d.length < 3) d = "0" + d;
    var cents = d.slice(-2);
    var ints = d.slice(0, -2);
    ints = ints.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    return ints + "," + cents;
  }

  function maskNfeKey(v) {
    return onlyDigits(v).slice(0, 44);
  }

  function applyMask(el) {
    var type = el.getAttribute("data-mask");
    if (!type) return;
    var start = el.selectionStart;
    var before = el.value;
    var next = before;
    if (type === "cpf") next = maskCpf(before);
    else if (type === "cnpj") next = maskCnpj(before);
    else if (type === "cpf-cnpj") next = maskCpfCnpj(before);
    else if (type === "phone") next = maskPhone(before);
    else if (type === "cep") next = maskCep(before);
    else if (type === "money") next = maskMoney(before);
    else if (type === "nfe-key") next = maskNfeKey(before);
    if (next !== before) {
      el.value = next;
      if (typeof start === "number" && type !== "money") {
        try {
          el.setSelectionRange(next.length, next.length);
        } catch (e) {}
      }
    }
  }

  document.addEventListener("input", function (e) {
    var el = e.target;
    if (!el || !el.getAttribute) return;
    if (el.getAttribute("data-mask")) applyMask(el);
  });

  document.querySelectorAll("[data-mask]").forEach(function (el) {
    if (el.value) applyMask(el);
  });

  // Auto-ativa busca em qualquer CEP / CNPJ do portal
  function markLookupFields(root) {
    (root || document).querySelectorAll('input[name="zipCode"], input[name="cep"], input[data-mask="cep"]').forEach(function (el) {
      el.setAttribute("data-cep-lookup", "1");
      if (!el.getAttribute("data-mask")) el.setAttribute("data-mask", "cep");
      el.setAttribute("inputmode", "numeric");
    });
    (root || document).querySelectorAll('input[data-mask="cnpj"], input[name="cnpjCampaign"], input[name*="cnpj"], input[name*="Cnpj"], input[data-cnpj-lookup]').forEach(function (el) {
      // CPF/CNPJ do fornecedor só busca quando tipo = CNPJ
      if (el.hasAttribute("data-supplier-document")) {
        var sel = document.querySelector("[data-supplier-doctype]");
        if (sel && sel.value !== "CNPJ") {
          el.removeAttribute("data-cnpj-lookup");
          return;
        }
      }
      el.setAttribute("data-cnpj-lookup", "1");
      if (!el.getAttribute("data-mask")) el.setAttribute("data-mask", "cnpj");
      el.setAttribute("inputmode", "numeric");
    });
  }
  markLookupFields(document);

  var lookupTimers = {};
  var lastCep = "";
  var lastCnpj = "";

  function setLookupStatus(form, kind, text, cls) {
    var status =
      (form && form.querySelector("[data-" + kind + "-status]")) ||
      document.querySelector("[data-" + kind + "-status]");
    if (!status) return;
    status.textContent = text || "";
    status.className = cls || "muted";
  }

  function findField(form, name) {
    var enabled = form.querySelector('[name="' + name + '"]:not([disabled])');
    if (enabled) return enabled;
    return form.querySelector('[name="' + name + '"]');
  }

  function fillFormFields(form, data, fields) {
    fields.forEach(function (name) {
      var field = findField(form, name);
      if (field && data[name] && !field.disabled) {
        field.value = data[name];
        if (field.getAttribute("data-mask")) applyMask(field);
      }
    });
  }

  function parseJsonResponse(r) {
    return r.text().then(function (text) {
      try {
        return JSON.parse(text || "{}");
      } catch (err) {
        return { ok: false, error: "Resposta inválida do servidor." };
      }
    });
  }

  function lookupCep(el) {
    var cep = onlyDigits(el.value);
    if (cep.length !== 8 || cep === lastCep) return;
    var form = el.closest("form") || document;
    setLookupStatus(form, "cep", "Buscando endereço…", "muted");
    fetch(base + "/api/cep.php?cep=" + encodeURIComponent(cep), { credentials: "same-origin" })
      .then(parseJsonResponse)
      .then(function (data) {
        if (!data.ok) {
          lastCep = "";
          setLookupStatus(form, "cep", data.error || "CEP não encontrado.", "muted");
          return;
        }
        lastCep = cep;
        fillFormFields(form, data, ["address", "neighborhood", "city", "state", "addressComplement"]);
        // Formulários sem bairro (ex.: cabos): embute no endereço
        var neighField = findField(form, "neighborhood");
        var addrField = findField(form, "address");
        if ((!neighField || neighField.disabled) && addrField && data.address) {
          var full = data.address;
          if (data.neighborhood) full += " — " + data.neighborhood;
          addrField.value = full;
        }
        if (data.cep) el.value = data.cep;
        setLookupStatus(form, "cep", "Endereço preenchido pelo CEP.", "muted");
      })
      .catch(function () {
        lastCep = "";
        setLookupStatus(form, "cep", "Falha ao buscar CEP.", "muted");
      });
  }

  function lookupCnpj(el) {
    var cnpj = onlyDigits(el.value);
    if (cnpj.length !== 14 || cnpj === lastCnpj) return;
    var form = el.closest("form") || document;
    setLookupStatus(form, "cnpj", "Buscando dados do CNPJ…", "muted");
    fetch(base + "/api/cnpj.php?cnpj=" + encodeURIComponent(cnpj), { credentials: "same-origin" })
      .then(parseJsonResponse)
      .then(function (data) {
        if (!data.ok) {
          lastCnpj = "";
          setLookupStatus(form, "cnpj", data.error || "CNPJ não encontrado.", "muted");
          return;
        }
        lastCnpj = cnpj;
        fillFormFields(form, data, [
          "name",
          "tradeName",
          "email",
          "phone",
          "zipCode",
          "address",
          "addressNumber",
          "addressComplement",
          "neighborhood",
          "city",
          "state",
          "stateRegistration",
        ]);
        if (el.name === "cnpjCampaign" && data.name) {
          setLookupStatus(form, "cnpj", "CNPJ encontrado: " + data.name, "muted");
        } else {
          var ieMsg = data.stateRegistration ? " · IE " + data.stateRegistration : "";
          setLookupStatus(form, "cnpj", "Dados do CNPJ preenchidos" + ieMsg + ".", "muted");
        }
        if (data.document) el.value = data.document;
        var zip = findField(form, "zipCode");
        if (zip && data.zipCode) {
          zip.value = data.zipCode;
          applyMask(zip);
          lastCep = "";
          lookupCep(zip);
        }
      })
      .catch(function () {
        lastCnpj = "";
        setLookupStatus(form, "cnpj", "Falha ao buscar CNPJ.", "muted");
      });
  }

  function scheduleLookup(el) {
    if (!el || !el.getAttribute) return;
    if (el.hasAttribute("data-cep-lookup") || el.getAttribute("data-mask") === "cep" || el.name === "zipCode" || el.name === "cep") {
      clearTimeout(lookupTimers.cep);
      lookupTimers.cep = setTimeout(function () {
        lookupCep(el);
      }, 250);
    }
    if (el.hasAttribute("data-cnpj-lookup")) {
      clearTimeout(lookupTimers.cnpj);
      lookupTimers.cnpj = setTimeout(function () {
        lookupCnpj(el);
      }, 300);
    }
  }

  document.addEventListener(
    "input",
    function (e) {
      var el = e.target;
      if (!el || !el.getAttribute) return;
      if (el.getAttribute("data-mask") === "cep" || el.name === "zipCode" || el.name === "cep") {
        if (onlyDigits(el.value).length === 8) scheduleLookup(el);
        else lastCep = "";
      }
      if (el.hasAttribute("data-cnpj-lookup") || el.getAttribute("data-mask") === "cnpj") {
        if (onlyDigits(el.value).length === 14) scheduleLookup(el);
        else lastCnpj = "";
      }
    },
    true
  );

  document.addEventListener(
    "blur",
    function (e) {
      scheduleLookup(e.target);
    },
    true
  );

  // Toggle CNPJ x CPF no cadastro de fornecedor
  function clearSupplierFormForDocTypeChange(form) {
    if (!form) return;
    var keep = { action: 1, id: 1, documentType: 1 };
    form.querySelectorAll("input,select,textarea").forEach(function (inp) {
      var name = inp.name || "";
      if (!name || keep[name]) return;
      if (inp.type === "hidden") return;
      if (inp.tagName === "SELECT") {
        inp.selectedIndex = 0;
        return;
      }
      if (inp.type === "checkbox" || inp.type === "radio") {
        inp.checked = false;
        return;
      }
      inp.value = "";
    });
    var state = form.querySelector('[name="state"]');
    if (state && !state.value) state.value = "GO";
    lastCep = "";
    lastCnpj = "";
    var cepStatus = form.querySelector("[data-cep-status]");
    if (cepStatus) {
      cepStatus.textContent = "Ao completar o CEP (8 dígitos), preenchemos o endereço.";
      cepStatus.className = "muted";
    }
  }

  function syncSupplierDocType() {
    var sel = document.querySelector("[data-supplier-doctype]");
    if (!sel) return;
    var type = sel.value;
    document.querySelectorAll("[data-show-when-doc]").forEach(function (el) {
      var when = el.getAttribute("data-show-when-doc");
      el.hidden = when !== type;
      el.querySelectorAll("input,select,textarea").forEach(function (inp) {
        if (when !== type) inp.disabled = true;
        else inp.disabled = false;
      });
    });
    var doc = document.querySelector("[data-supplier-document]");
    if (doc) {
      doc.setAttribute("data-mask", type === "CPF" ? "cpf" : "cnpj");
      doc.setAttribute("placeholder", type === "CPF" ? "000.000.000-00" : "00.000.000/0000-00");
      doc.setAttribute("maxlength", type === "CPF" ? "14" : "18");
      applyMask(doc);
      // Busca automática somente para CNPJ — nunca para CPF
      if (type === "CNPJ") doc.setAttribute("data-cnpj-lookup", "1");
      else doc.removeAttribute("data-cnpj-lookup");
    }
    var cnpjStatus = document.querySelector("[data-cnpj-status]");
    if (cnpjStatus) {
      cnpjStatus.textContent =
        type === "CNPJ"
          ? "Ao completar o CNPJ (14 dígitos), buscamos razão social, endereço e inscrição estadual."
          : "Informe o CPF manualmente (sem busca automática).";
      cnpjStatus.className = "muted";
    }
  }
  var docTypeSel = document.querySelector("[data-supplier-doctype]");
  if (docTypeSel) {
    docTypeSel.addEventListener("change", function () {
      var form = docTypeSel.closest("form");
      clearSupplierFormForDocTypeChange(form);
      syncSupplierDocType();
    });
    syncSupplierDocType();
  }

  // Garante que campo CPF do fornecedor nunca dispare busca de CNPJ
  document.addEventListener(
    "input",
    function (e) {
      var el = e.target;
      if (!el || !el.hasAttribute || !el.hasAttribute("data-supplier-document")) return;
      var sel = document.querySelector("[data-supplier-doctype]");
      if (sel && sel.value === "CPF") {
        el.removeAttribute("data-cnpj-lookup");
      }
    },
    true
  );

  // Painel Configurações (engrenagem) — tablet/celular
  (function () {
    var sheet = document.querySelector("[data-config-sheet]");
    var overlay = document.querySelector("[data-config-overlay]");
    if (!sheet) return;
    function openConfig() {
      sheet.hidden = false;
      if (overlay) overlay.hidden = false;
      document.body.classList.add("config-sheet-open");
      var closeBtn = sheet.querySelector("[data-config-close]");
      if (closeBtn) closeBtn.focus();
    }
    function closeConfig() {
      sheet.hidden = true;
      if (overlay) overlay.hidden = true;
      document.body.classList.remove("config-sheet-open");
    }
    document.querySelectorAll("[data-config-open]").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        openConfig();
      });
    });
    document.querySelectorAll("[data-config-close]").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        closeConfig();
      });
    });
    if (overlay) {
      overlay.addEventListener("click", closeConfig);
    }
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !sheet.hidden) closeConfig();
    });
  })();

  // Ir ao topo — barra mobile + botão flutuante
  function scrollPageTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
    document.documentElement.scrollTo({ top: 0, behavior: "smooth" });
    document.body.scrollTo({ top: 0, behavior: "smooth" });
    var main = document.querySelector(".admin-main, .dash-main");
    if (main) main.scrollTo({ top: 0, behavior: "smooth" });
    var side = document.querySelector(".sidebar");
    if (side) side.scrollTo({ top: 0, behavior: "smooth" });
  }
  document.querySelectorAll("[data-scroll-top]").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      scrollPageTop();
    });
  });
  var fab = document.querySelector(".back-to-top");
  if (fab) {
    var toggleFab = function () {
      var y = window.scrollY || document.documentElement.scrollTop || 0;
      fab.classList.toggle("is-visible", y > 280);
    };
    window.addEventListener("scroll", toggleFab, { passive: true });
    toggleFab();
  }

  document.addEventListener("keydown", function (e) {
    if (!e.key) return;
    var tag = (e.target && e.target.tagName) || "";
    if (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT" || e.target.isContentEditable) return;
    if (e.ctrlKey || e.metaKey || e.altKey) return;

    var fMap = {
      F2: "/admin/receitas.php",
      F3: "/admin/despesas.php",
      F4: "/admin/lancamento.php?tipo=DESPESA",
      F5: "/admin/movimentacoes.php",
      F6: "/admin/diario.php",
      F7: "/admin/vinculos.php",
      F8: "/admin/conciliacao.php",
      Home: "/admin/index.php",
    };
    if (fMap[e.key]) {
      e.preventDefault();
      window.location.href = base + fMap[e.key];
      return;
    }

    // Atalhos por letra (tablet/celular sem F-keys): I início, R receita, D despesa, L lançar, M movimentação
    var letterMap = {
      i: "/admin/index.php",
      I: "/admin/index.php",
      r: "/admin/receitas.php",
      R: "/admin/receitas.php",
      d: "/admin/despesas.php",
      D: "/admin/despesas.php",
      l: "/admin/lancamento.php",
      L: "/admin/lancamento.php",
      m: "/admin/movimentacoes.php",
      M: "/admin/movimentacoes.php",
    };
    if (letterMap[e.key]) {
      e.preventDefault();
      window.location.href = base + letterMap[e.key];
      return;
    }

    if (/^[1-9]$/.test(e.key)) {
      var tile = document.querySelector('[data-quick-key="' + e.key + '"]');
      if (tile && tile.getAttribute("href")) {
        e.preventDefault();
        window.location.href = tile.getAttribute("href");
      }
    }
  });

  var nfeInput = document.querySelector("[data-nfe-input]");
  var nfeStatus = document.querySelector("[data-nfe-status]");
  if (nfeInput && nfeStatus) {
    var timer = null;
    nfeInput.addEventListener("input", function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        var key = onlyDigits(nfeInput.value);
        if (key.length < 44) {
          nfeStatus.textContent = "Opcional. Se informar, use a chave completa (44 dígitos).";
          nfeStatus.className = "muted";
          return;
        }
        fetch(base + "/api/nfe.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ key: key }),
        })
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            if (data.valid) {
              nfeStatus.textContent = "NF-e válida · modelo " + (data.parsed && data.parsed.model);
              nfeStatus.className = "badge badge-ok";
              var form = nfeInput.closest("form");
              if (form && data.parsed) {
                var modelo = form.querySelector('[name="modelo"]');
                var serie = form.querySelector('[name="numeroSerie"]');
                var numero = form.querySelector('[name="numeroNf"]');
                if (modelo && !modelo.value) modelo.value = data.parsed.model || "";
                if (serie && !serie.value) serie.value = String(parseInt(data.parsed.series || "0", 10) || "");
                if (numero && !numero.value) numero.value = String(parseInt(data.parsed.number || "0", 10) || "");
              }
            } else {
              nfeStatus.textContent = (data.reason || "NF-e inválida") + " — pode registrar mesmo assim.";
              nfeStatus.className = "badge badge-warn";
            }
          })
          .catch(function () {
            nfeStatus.textContent = "Falha ao validar";
            nfeStatus.className = "badge badge-warn";
          });
      }, 350);
    });
  }

  // Lançamento: troca Receita/Despesa + CPF/CNPJ por conta + submit com aviso visível
  (function initLancamentoForm() {
    var form = document.querySelector("[data-lancamento-form]");
    if (!form) return;

    var accountSel = form.querySelector("[data-bank-account]");
    var donorFields = form.querySelector("[data-donor-fields]");
    var donorWait = form.querySelector("[data-donor-wait]");
    var typePicker = form.querySelector("[data-donor-type-picker]");
    var docLabel = form.querySelector("[data-donor-doc-label]");
    var docInput = form.querySelector("[data-donor-doc-input]");
    var nameInput = form.querySelector("[data-donor-name]");
    var typeRadios = form.querySelectorAll("[data-donor-doc-type]");
    var docTypeValue = form.querySelector("[data-donor-doc-type-value]");
    var accountHint = form.querySelector("[data-account-hint]");
    var formError = document.querySelector("[data-form-error]");
    var docError = form.querySelector("[data-donor-doc-error]");
    var amountInput = form.querySelector('[name="amount"]');
    var supplierSel = form.querySelector('[name="supplierId"]');

    function selectedKind() {
      var checked = form.querySelector("[data-kind-toggle]:checked");
      return checked ? checked.value : "DESPESA";
    }

    function selectedAccountOption() {
      if (!accountSel) return null;
      return accountSel.options[accountSel.selectedIndex] || null;
    }

    function showFormError(message, focusEl) {
      if (formError) {
        formError.hidden = false;
        formError.textContent = message;
        formError.removeAttribute("data-server-error");
      }
      if (focusEl) {
        try {
          focusEl.classList.add("is-invalid-input");
          focusEl.focus({ preventScroll: false });
          focusEl.scrollIntoView({ block: "center", behavior: "smooth" });
        } catch (e) {
          try {
            focusEl.focus();
          } catch (e2) {}
        }
      }
    }

    function clearFormError() {
      if (formError && !formError.getAttribute("data-server-error")) {
        formError.hidden = true;
        formError.textContent = "";
      }
      form.querySelectorAll(".is-invalid-input").forEach(function (el) {
        el.classList.remove("is-invalid-input");
      });
      if (docError) {
        docError.hidden = true;
        docError.textContent = "";
      }
    }

    function setKindBlocks() {
      var kind = selectedKind();
      form.querySelectorAll("[data-kind-block]").forEach(function (block) {
        var active = block.getAttribute("data-kind-block") === kind;
        block.hidden = !active;
        block.querySelectorAll("input, select, textarea, button").forEach(function (el) {
          if (el.matches("[data-kind-toggle]")) return;
          if (el.hasAttribute("data-locked-disabled")) return;
          el.disabled = !active;
        });
      });
    }

    function applyDocType(docType) {
      if (docTypeValue) {
        docTypeValue.disabled = false;
        docTypeValue.value = docType || "";
      }
      if (!docInput || !docLabel) return;
      var isCnpj = docType === "CNPJ";
      docLabel.textContent = isCnpj ? "CNPJ do doador" : "CPF do doador";
      docInput.setAttribute("data-mask", isCnpj ? "cnpj" : "cpf");
      docInput.setAttribute("placeholder", isCnpj ? "00.000.000/0000-00" : "000.000.000-00");
      docInput.setAttribute("inputmode", "numeric");
      if (isCnpj) docInput.setAttribute("data-cnpj-lookup", "1");
      else docInput.removeAttribute("data-cnpj-lookup");
      var digits = onlyDigits(docInput.value);
      if (!digits) docInput.value = "";
      else if (isCnpj) docInput.value = maskCnpj(digits);
      else docInput.value = maskCpf(digits.slice(0, 11));
      applyMask(docInput);
      markLookupFields(form);
    }

    function syncRevenueDonor() {
      var isReceita = selectedKind() === "RECEITA";
      if (accountHint) {
        accountHint.hidden = !isReceita;
        accountHint.textContent = isReceita
          ? "Em receita, a conta define se o doador informa CPF ou CNPJ."
          : "Deixe em branco para usar o vínculo padrão da categoria.";
      }
      var emptyOpt = accountSel && accountSel.querySelector('option[value=""]');
      if (emptyOpt) {
        emptyOpt.textContent = isReceita
          ? "— selecione a conta —"
          : "Usar vínculo padrão da categoria";
      }

      if (!donorFields) return;

      if (!isReceita) {
        donorFields.hidden = true;
        if (donorWait) donorWait.hidden = true;
        return;
      }

      var opt = selectedAccountOption();
      var allowCpf = !!(opt && opt.getAttribute("data-deposit-cpf") === "1");
      var allowCnpj = !!(opt && opt.getAttribute("data-deposit-cnpj") === "1");
      var hasAccount = !!(opt && opt.value);

      if (!hasAccount || (!allowCpf && !allowCnpj)) {
        donorFields.hidden = true;
        if (donorWait) donorWait.hidden = false;
        return;
      }

      donorFields.hidden = false;
      if (donorWait) donorWait.hidden = true;

      var both = allowCpf && allowCnpj;
      if (typePicker) typePicker.hidden = !both;

      var chosen = "";
      typeRadios.forEach(function (r) {
        var allowed = (r.value === "CPF" && allowCpf) || (r.value === "CNPJ" && allowCnpj);
        r.disabled = !allowed;
        if (!both) r.checked = allowed;
        if (r.checked && allowed) chosen = r.value;
      });
      if (!chosen) chosen = allowCnpj && !allowCpf ? "CNPJ" : "CPF";
      typeRadios.forEach(function (r) {
        if (!both) r.checked = r.value === chosen;
      });
      applyDocType(chosen);
    }

    var installChk = form.querySelector("[data-installments-2x]");
    var installToggle = form.querySelector("[data-installments-toggle]");
    var installPanel = form.querySelector("[data-installments-panel]");
    var singleDateField = form.querySelector("[data-single-date-field]");
    var amountHint = form.querySelector("[data-amount-total-hint]");
    var instAmt1 = form.querySelector('[data-inst-amount="1"]');
    var instAmt2 = form.querySelector('[data-inst-amount="2"]');
    var instDate1 = form.querySelector('[data-inst-date="1"]');
    var instDate2 = form.querySelector('[data-inst-date="2"]');
    var splittingFromTotal = false;
    var summingFromParts = false;

    function moneyFromCents(cents) {
      return (cents / 100).toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    function addMonthsIso(iso, months) {
      if (!iso) return "";
      var p = String(iso).split("-");
      if (p.length < 3) return iso;
      var d = new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
      d.setMonth(d.getMonth() + months);
      var mm = String(d.getMonth() + 1).padStart(2, "0");
      var dd = String(d.getDate()).padStart(2, "0");
      return d.getFullYear() + "-" + mm + "-" + dd;
    }

    function fillInstallmentsFromTotal(force) {
      if (!instAmt1 || !instAmt2 || !amountInput) return;
      var cents = parseInt(onlyDigits(amountInput.value) || "0", 10);
      if (cents <= 0) return;
      var empty =
        !onlyDigits(instAmt1.value) && !onlyDigits(instAmt2.value);
      if (!force && !empty) return;
      var a = Math.floor(cents / 2);
      var b = cents - a;
      splittingFromTotal = true;
      instAmt1.value = moneyFromCents(a);
      instAmt2.value = moneyFromCents(b);
      applyMask(instAmt1);
      applyMask(instAmt2);
      splittingFromTotal = false;
      var singleDate = form.querySelector("[data-single-date]");
      if (instDate1 && (!instDate1.value || force) && singleDate && singleDate.value) {
        instDate1.value = singleDate.value;
      }
      if (instDate2 && (!instDate2.value || force) && instDate1 && instDate1.value) {
        instDate2.value = addMonthsIso(instDate1.value, 1);
      }
    }

    function sumInstallmentsToTotal() {
      if (!amountInput || !instAmt1 || !instAmt2 || splittingFromTotal) return;
      var a = parseInt(onlyDigits(instAmt1.value) || "0", 10);
      var b = parseInt(onlyDigits(instAmt2.value) || "0", 10);
      if (a <= 0 && b <= 0) return;
      summingFromParts = true;
      amountInput.value = moneyFromCents(a + b);
      applyMask(amountInput);
      summingFromParts = false;
    }

    function syncInstallmentsUI() {
      var isDespesa = selectedKind() === "DESPESA";
      var twoX = !!(installChk && installChk.checked && isDespesa);
      if (installToggle) installToggle.hidden = !isDespesa;
      if (installPanel) {
        installPanel.hidden = !twoX;
        installPanel.querySelectorAll("input").forEach(function (el) {
          el.disabled = !twoX;
        });
      }
      if (singleDateField) {
        // Receita: data ao lado do valor. Despesa 1x: data na linha seguinte. Despesa 2x: oculta.
        singleDateField.hidden = twoX;
        var singleDate = singleDateField.querySelector("[data-single-date]");
        if (singleDate) singleDate.disabled = twoX;
      }
      if (amountHint) amountHint.hidden = !twoX;
      if (amountInput) {
        amountInput.readOnly = twoX;
        if (twoX) amountInput.setAttribute("data-locked-total", "1");
        else amountInput.removeAttribute("data-locked-total");
      }
      if (twoX) {
        fillInstallmentsFromTotal(false);
        sumInstallmentsToTotal();
      }
    }

    function syncAll() {
      setKindBlocks();
      syncRevenueDonor();
      syncInstallmentsUI();
    }

    function validateDonorDocument(strict) {
      if (selectedKind() !== "RECEITA") return true;
      if (!docInput || donorFields.hidden) return true;
      var type = (docTypeValue && docTypeValue.value) || "CPF";
      var digits = onlyDigits(docInput.value);
      if (!digits) {
        if (!strict) return true;
        var msgEmpty =
          type === "CNPJ"
            ? "Informe o CNPJ do doador para continuar."
            : "Informe o CPF do doador para continuar.";
        if (docError) {
          docError.hidden = false;
          docError.textContent = msgEmpty;
        }
        showFormError(msgEmpty, docInput);
        return false;
      }
      if (type === "CNPJ") {
        if (digits.length < 14 && !strict) return true;
        if (!isValidCnpj(digits)) {
          var msgCnpj = "CNPJ do doador inválido. Corrija os dígitos e tente novamente.";
          if (docError) {
            docError.hidden = false;
            docError.textContent = msgCnpj;
          }
          showFormError(msgCnpj, docInput);
          return false;
        }
      } else {
        if (digits.length < 11 && !strict) return true;
        if (!isValidCpf(digits)) {
          var msgCpf = "CPF do doador inválido. Corrija os dígitos e tente novamente.";
          if (docError) {
            docError.hidden = false;
            docError.textContent = msgCpf;
          }
          showFormError(msgCpf, docInput);
          return false;
        }
      }
      if (docError) {
        docError.hidden = true;
        docError.textContent = "";
      }
      if (docInput) docInput.classList.remove("is-invalid-input");
      return true;
    }

    function validateBeforeSubmit() {
      clearFormError();
      syncAll();

      var kind = selectedKind();
      if (kind === "DESPESA" && installChk && installChk.checked) {
        sumInstallmentsToTotal();
        var a1 = parseInt(onlyDigits(instAmt1 ? instAmt1.value : "") || "0", 10);
        var a2 = parseInt(onlyDigits(instAmt2 ? instAmt2.value : "") || "0", 10);
        if (a1 <= 0) {
          showFormError("Informe o valor da 1ª parcela.", instAmt1);
          return false;
        }
        if (a2 <= 0) {
          showFormError("Informe o valor da 2ª parcela.", instAmt2);
          return false;
        }
        if (!instDate1 || !instDate1.value) {
          showFormError("Informe a data de pagamento da 1ª parcela.", instDate1);
          return false;
        }
        if (!instDate2 || !instDate2.value) {
          showFormError("Informe a data de pagamento da 2ª parcela.", instDate2);
          return false;
        }
      } else if (amountInput) {
        var amountDigits = onlyDigits(amountInput.value);
        if (!amountDigits || parseInt(amountDigits, 10) <= 0) {
          showFormError("Informe o valor do lançamento.", amountInput);
          return false;
        }
      }

      if (kind === "RECEITA") {
        if (!accountSel || !accountSel.value) {
          showFormError("Selecione a conta bancária da receita.", accountSel);
          return false;
        }
        if (nameInput && !String(nameInput.value || "").trim()) {
          showFormError("Informe o nome do doador.", nameInput);
          return false;
        }
        if (!validateDonorDocument(true)) return false;
        // Garante donorDocType no POST (campo hidden fora do bloco)
        if (docTypeValue) docTypeValue.disabled = false;
      } else {
        if (supplierSel && !supplierSel.value) {
          showFormError("Selecione o fornecedor da despesa.", supplierSel);
          return false;
        }
      }
      return true;
    }

    if (formError && formError.textContent && !formError.hidden) {
      formError.setAttribute("data-server-error", "1");
    }

    form.querySelectorAll("[data-kind-toggle]").forEach(function (r) {
      r.addEventListener("change", function () {
        clearFormError();
        syncAll();
      });
    });
    if (installChk) {
      installChk.addEventListener("change", function () {
        if (installChk.checked) fillInstallmentsFromTotal(true);
        syncInstallmentsUI();
      });
    }
    if (amountInput) {
      amountInput.addEventListener("input", function () {
        if (summingFromParts) return;
        if (installChk && installChk.checked && selectedKind() === "DESPESA") {
          fillInstallmentsFromTotal(true);
        }
      });
    }
    [instAmt1, instAmt2].forEach(function (el) {
      if (!el) return;
      el.addEventListener("input", function () {
        if (installChk && installChk.checked) sumInstallmentsToTotal();
      });
    });
    if (accountSel) {
      accountSel.addEventListener("change", function () {
        clearFormError();
        syncRevenueDonor();
      });
    }
    typeRadios.forEach(function (r) {
      r.addEventListener("change", function () {
        if (!r.checked) return;
        clearFormError();
        applyDocType(r.value);
        validateDonorDocument(false);
      });
    });
    if (docInput) {
      docInput.addEventListener("input", function () {
        if (formError) formError.removeAttribute("data-server-error");
        docInput.classList.remove("is-invalid-input");
        validateDonorDocument(false);
      });
      docInput.addEventListener("blur", function () {
        validateDonorDocument(true);
      });
    }

    form.addEventListener("submit", function (e) {
      if (!validateBeforeSubmit()) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      var btn = form.querySelector("[data-lancamento-submit]");
      if (btn) {
        btn.disabled = true;
        btn.textContent = "Registrando…";
      }
      return true;
    });

    syncAll();
  })();

  // ===== PWA: service worker + instalar app =====
  (function initPwa() {
    var swUrl = (base || "") + "/sw.js";
    var scope = (base || "") + "/";
    if ("serviceWorker" in navigator) {
      window.addEventListener("load", function () {
        navigator.serviceWorker.register(swUrl, { scope: scope }).catch(function () {});
      });
    }

    var deferredPrompt = null;
    var banner = document.querySelector("[data-pwa-install]");
    var btnInstall = document.querySelector("[data-pwa-install-btn]");
    var btnDismiss = document.querySelector("[data-pwa-install-dismiss]");
    var dismissedKey = "pwa-install-dismissed";

    function isStandalone() {
      return (
        window.matchMedia("(display-mode: standalone)").matches ||
        window.navigator.standalone === true
      );
    }

    function showBanner() {
      if (!banner || isStandalone()) return;
      if (localStorage.getItem(dismissedKey) === "1") return;
      banner.hidden = false;
      document.body.classList.add("pwa-install-visible");
    }

    function hideBanner() {
      if (!banner) return;
      banner.hidden = true;
      document.body.classList.remove("pwa-install-visible");
    }

    window.addEventListener("beforeinstallprompt", function (e) {
      e.preventDefault();
      deferredPrompt = e;
      showBanner();
    });

    if (btnInstall) {
      btnInstall.addEventListener("click", function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.finally(function () {
          deferredPrompt = null;
          hideBanner();
        });
      });
    }
    if (btnDismiss) {
      btnDismiss.addEventListener("click", function () {
        localStorage.setItem(dismissedKey, "1");
        hideBanner();
      });
    }

    window.addEventListener("appinstalled", function () {
      hideBanner();
      deferredPrompt = null;
    });

    // iOS: dica leve (não há beforeinstallprompt)
    var isIos =
      /iphone|ipad|ipod/i.test(navigator.userAgent || "") &&
      !window.MSStream;
    if (isIos && !isStandalone() && banner && localStorage.getItem(dismissedKey) !== "1") {
      var copy = banner.querySelector(".pwa-install-copy span");
      if (copy) {
        copy.textContent = "No Safari: Compartilhar → Adicionar à Tela de Início";
      }
      if (btnInstall) btnInstall.hidden = true;
      showBanner();
    }
  })();

})();

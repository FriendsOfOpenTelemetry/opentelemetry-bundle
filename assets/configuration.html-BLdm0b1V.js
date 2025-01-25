import{_ as s,c as a,b as e,o as l}from"./app-ruHJBY2T.js";const p={};function t(i,n){return l(),a("div",null,n[0]||(n[0]=[e(`<h1 id="configuration" tabindex="-1"><a class="header-anchor" href="#configuration"><span>Configuration</span></a></h1><p>To get a full list of configuration options, run the following command:</p><div class="language-bash line-numbers-mode" data-highlighter="prismjs" data-ext="sh" data-title="sh"><pre><code><span class="line">bin/console config:dump-reference open_telemetry</span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div></div></div><h2 id="full-configuration-reference" tabindex="-1"><a class="header-anchor" href="#full-configuration-reference"><span>Full configuration reference</span></a></h2><div class="language-yaml line-numbers-mode" data-highlighter="prismjs" data-ext="yml" data-title="yml"><pre><code><span class="line"></span>
<span class="line"><span class="token comment"># Default configuration for extension with alias: &quot;open_telemetry&quot;</span></span>
<span class="line"><span class="token key atrule">open_telemetry</span><span class="token punctuation">:</span></span>
<span class="line">    <span class="token key atrule">service</span><span class="token punctuation">:</span></span>
<span class="line">        <span class="token key atrule">namespace</span><span class="token punctuation">:</span>            <span class="token null important">~</span> <span class="token comment"># Required, Example: MyOrganization</span></span>
<span class="line">        <span class="token key atrule">name</span><span class="token punctuation">:</span>                 <span class="token null important">~</span> <span class="token comment"># Required, Example: MyApp</span></span>
<span class="line">        <span class="token key atrule">version</span><span class="token punctuation">:</span>              <span class="token null important">~</span> <span class="token comment"># Required, Example: 1.0.0</span></span>
<span class="line">        <span class="token key atrule">environment</span><span class="token punctuation">:</span>          <span class="token null important">~</span> <span class="token comment"># Required, Example: &#39;%kernel.environment%&#39;</span></span>
<span class="line">    <span class="token key atrule">instrumentation</span><span class="token punctuation">:</span></span>
<span class="line">        <span class="token key atrule">cache</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">tracing</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The tracer to use, defaults to \`traces.default_tracer\` or first tracer in \`traces.tracers\`</span></span>
<span class="line">                <span class="token key atrule">tracer</span><span class="token punctuation">:</span>               <span class="token null important">~</span></span>
<span class="line">            <span class="token key atrule">metering</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The meter to use, defaults to \`metrics.default_meter\` or first meter in \`metrics.meters\`</span></span>
<span class="line">                <span class="token key atrule">meter</span><span class="token punctuation">:</span>                <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">console</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">type</span><span class="token punctuation">:</span>                 auto <span class="token comment"># One of &quot;auto&quot;; &quot;attribute&quot;</span></span>
<span class="line">            <span class="token key atrule">tracing</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The tracer to use, defaults to \`traces.default_tracer\` or first tracer in \`traces.tracers\`</span></span>
<span class="line">                <span class="token key atrule">tracer</span><span class="token punctuation">:</span>               <span class="token null important">~</span></span>
<span class="line">            <span class="token key atrule">metering</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The meter to use, defaults to \`metrics.default_meter\` or first meter in \`metrics.meters\`</span></span>
<span class="line">                <span class="token key atrule">meter</span><span class="token punctuation">:</span>                <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">doctrine</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">tracing</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The tracer to use, defaults to \`traces.default_tracer\` or first tracer in \`traces.tracers\`</span></span>
<span class="line">                <span class="token key atrule">tracer</span><span class="token punctuation">:</span>               <span class="token null important">~</span></span>
<span class="line">            <span class="token key atrule">metering</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The meter to use, defaults to \`metrics.default_meter\` or first meter in \`metrics.meters\`</span></span>
<span class="line">                <span class="token key atrule">meter</span><span class="token punctuation">:</span>                <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">http_client</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">tracing</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The tracer to use, defaults to \`traces.default_tracer\` or first tracer in \`traces.tracers\`</span></span>
<span class="line">                <span class="token key atrule">tracer</span><span class="token punctuation">:</span>               <span class="token null important">~</span></span>
<span class="line">            <span class="token key atrule">metering</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The meter to use, defaults to \`metrics.default_meter\` or first meter in \`metrics.meters\`</span></span>
<span class="line">                <span class="token key atrule">meter</span><span class="token punctuation">:</span>                <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">http_kernel</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">type</span><span class="token punctuation">:</span>                 auto <span class="token comment"># One of &quot;auto&quot;; &quot;attribute&quot;</span></span>
<span class="line">            <span class="token key atrule">tracing</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The tracer to use, defaults to \`traces.default_tracer\` or first tracer in \`traces.tracers\`</span></span>
<span class="line">                <span class="token key atrule">tracer</span><span class="token punctuation">:</span>               <span class="token null important">~</span></span>
<span class="line">            <span class="token key atrule">metering</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The meter to use, defaults to \`metrics.default_meter\` or first meter in \`metrics.meters\`</span></span>
<span class="line">                <span class="token key atrule">meter</span><span class="token punctuation">:</span>                <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">mailer</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">tracing</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The tracer to use, defaults to \`traces.default_tracer\` or first tracer in \`traces.tracers\`</span></span>
<span class="line">                <span class="token key atrule">tracer</span><span class="token punctuation">:</span>               <span class="token null important">~</span></span>
<span class="line">            <span class="token key atrule">metering</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The meter to use, defaults to \`metrics.default_meter\` or first meter in \`metrics.meters\`</span></span>
<span class="line">                <span class="token key atrule">meter</span><span class="token punctuation">:</span>                <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">messenger</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">type</span><span class="token punctuation">:</span>                 auto <span class="token comment"># One of &quot;auto&quot;; &quot;attribute&quot;</span></span>
<span class="line">            <span class="token key atrule">tracing</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The tracer to use, defaults to \`traces.default_tracer\` or first tracer in \`traces.tracers\`</span></span>
<span class="line">                <span class="token key atrule">tracer</span><span class="token punctuation">:</span>               <span class="token null important">~</span></span>
<span class="line">            <span class="token key atrule">metering</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The meter to use, defaults to \`metrics.default_meter\` or first meter in \`metrics.meters\`</span></span>
<span class="line">                <span class="token key atrule">meter</span><span class="token punctuation">:</span>                <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">twig</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">tracing</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The tracer to use, defaults to \`traces.default_tracer\` or first tracer in \`traces.tracers\`</span></span>
<span class="line">                <span class="token key atrule">tracer</span><span class="token punctuation">:</span>               <span class="token null important">~</span></span>
<span class="line">            <span class="token key atrule">metering</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># The meter to use, defaults to \`metrics.default_meter\` or first meter in \`metrics.meters\`</span></span>
<span class="line">                <span class="token key atrule">meter</span><span class="token punctuation">:</span>                <span class="token null important">~</span></span>
<span class="line">    <span class="token key atrule">traces</span><span class="token punctuation">:</span></span>
<span class="line">        <span class="token key atrule">tracers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">tracer</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">name</span><span class="token punctuation">:</span>                 <span class="token null important">~</span></span>
<span class="line">                <span class="token key atrule">version</span><span class="token punctuation">:</span>              <span class="token null important">~</span></span>
<span class="line">                <span class="token key atrule">provider</span><span class="token punctuation">:</span>             <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">        <span class="token key atrule">providers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">provider</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">type</span><span class="token punctuation">:</span>                 default <span class="token comment"># One of &quot;default&quot;; &quot;noop&quot;, Required</span></span>
<span class="line">                <span class="token key atrule">sampler</span><span class="token punctuation">:</span></span>
<span class="line">                    <span class="token key atrule">type</span><span class="token punctuation">:</span>                 always_on <span class="token comment"># One of &quot;always_off&quot;; &quot;always_on&quot;; &quot;parent_based_always_off&quot;; &quot;parent_based_always_on&quot;; &quot;parent_based_trace_id_ratio&quot;; &quot;trace_id_ratio&quot;; &quot;attribute_based&quot;, Required</span></span>
<span class="line">                    <span class="token key atrule">options</span><span class="token punctuation">:</span>              <span class="token punctuation">[</span><span class="token punctuation">]</span></span>
<span class="line">                <span class="token key atrule">processors</span><span class="token punctuation">:</span>           <span class="token punctuation">[</span><span class="token punctuation">]</span></span>
<span class="line">        <span class="token key atrule">processors</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">processor</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">type</span><span class="token punctuation">:</span>                 simple <span class="token comment"># One of &quot;multi&quot;; &quot;simple&quot;; &quot;noop&quot;, Required</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># Required if processor type is multi</span></span>
<span class="line">                <span class="token key atrule">processors</span><span class="token punctuation">:</span>           <span class="token punctuation">[</span><span class="token punctuation">]</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># Required if processor type is simple or batch</span></span>
<span class="line">                <span class="token key atrule">exporter</span><span class="token punctuation">:</span>             <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">exporters</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">exporter</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">dsn</span><span class="token punctuation">:</span>                  <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                <span class="token key atrule">options</span><span class="token punctuation">:</span></span>
<span class="line">                    <span class="token key atrule">format</span><span class="token punctuation">:</span>               json <span class="token comment"># One of &quot;json&quot;; &quot;ndjson&quot;; &quot;gprc&quot;; &quot;protobuf&quot;</span></span>
<span class="line">                    <span class="token key atrule">compression</span><span class="token punctuation">:</span>          none <span class="token comment"># One of &quot;gzip&quot;; &quot;none&quot;</span></span>
<span class="line">                    <span class="token key atrule">headers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">                        <span class="token comment"># Prototype</span></span>
<span class="line">                        <span class="token punctuation">-</span></span>
<span class="line">                            <span class="token key atrule">name</span><span class="token punctuation">:</span>                 <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                            <span class="token key atrule">value</span><span class="token punctuation">:</span>                <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                    <span class="token key atrule">timeout</span><span class="token punctuation">:</span>              <span class="token number">0.1</span></span>
<span class="line">                    <span class="token key atrule">retry</span><span class="token punctuation">:</span>                <span class="token number">100</span></span>
<span class="line">                    <span class="token key atrule">max</span><span class="token punctuation">:</span>                  <span class="token number">3</span></span>
<span class="line">                    <span class="token key atrule">ca</span><span class="token punctuation">:</span>                   <span class="token null important">~</span></span>
<span class="line">                    <span class="token key atrule">cert</span><span class="token punctuation">:</span>                 <span class="token null important">~</span></span>
<span class="line">                    <span class="token key atrule">key</span><span class="token punctuation">:</span>                  <span class="token null important">~</span></span>
<span class="line">    <span class="token key atrule">metrics</span><span class="token punctuation">:</span></span>
<span class="line">        <span class="token key atrule">meters</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">meter</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">name</span><span class="token punctuation">:</span>                 <span class="token null important">~</span></span>
<span class="line">                <span class="token key atrule">provider</span><span class="token punctuation">:</span>             <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">        <span class="token key atrule">providers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">provider</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">type</span><span class="token punctuation">:</span>                 default <span class="token comment"># One of &quot;noop&quot;; &quot;default&quot;, Required</span></span>
<span class="line">                <span class="token key atrule">exporter</span><span class="token punctuation">:</span>             <span class="token null important">~</span></span>
<span class="line">                <span class="token key atrule">filter</span><span class="token punctuation">:</span>               none <span class="token comment"># One of &quot;all&quot;; &quot;none&quot;; &quot;with_sampled_trace&quot;</span></span>
<span class="line">        <span class="token key atrule">exporters</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">exporter</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">dsn</span><span class="token punctuation">:</span>                  <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                <span class="token key atrule">temporality</span><span class="token punctuation">:</span>          delta <span class="token comment"># One of &quot;delta&quot;; &quot;cumulative&quot;; &quot;low_memory&quot;</span></span>
<span class="line">                <span class="token key atrule">options</span><span class="token punctuation">:</span></span>
<span class="line">                    <span class="token key atrule">format</span><span class="token punctuation">:</span>               json <span class="token comment"># One of &quot;json&quot;; &quot;ndjson&quot;; &quot;gprc&quot;; &quot;protobuf&quot;</span></span>
<span class="line">                    <span class="token key atrule">compression</span><span class="token punctuation">:</span>          none <span class="token comment"># One of &quot;gzip&quot;; &quot;none&quot;</span></span>
<span class="line">                    <span class="token key atrule">headers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">                        <span class="token comment"># Prototype</span></span>
<span class="line">                        <span class="token punctuation">-</span></span>
<span class="line">                            <span class="token key atrule">name</span><span class="token punctuation">:</span>                 <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                            <span class="token key atrule">value</span><span class="token punctuation">:</span>                <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                    <span class="token key atrule">timeout</span><span class="token punctuation">:</span>              <span class="token number">0.1</span></span>
<span class="line">                    <span class="token key atrule">retry</span><span class="token punctuation">:</span>                <span class="token number">100</span></span>
<span class="line">                    <span class="token key atrule">max</span><span class="token punctuation">:</span>                  <span class="token number">3</span></span>
<span class="line">                    <span class="token key atrule">ca</span><span class="token punctuation">:</span>                   <span class="token null important">~</span></span>
<span class="line">                    <span class="token key atrule">cert</span><span class="token punctuation">:</span>                 <span class="token null important">~</span></span>
<span class="line">                    <span class="token key atrule">key</span><span class="token punctuation">:</span>                  <span class="token null important">~</span></span>
<span class="line">    <span class="token key atrule">logs</span><span class="token punctuation">:</span></span>
<span class="line">        <span class="token key atrule">monolog</span><span class="token punctuation">:</span></span>
<span class="line">            <span class="token key atrule">enabled</span><span class="token punctuation">:</span>              <span class="token boolean important">false</span></span>
<span class="line">            <span class="token key atrule">handlers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># Prototype</span></span>
<span class="line">                <span class="token key atrule">handler</span><span class="token punctuation">:</span></span>
<span class="line">                    <span class="token key atrule">provider</span><span class="token punctuation">:</span>             <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                    <span class="token key atrule">level</span><span class="token punctuation">:</span>                debug <span class="token comment"># One of &quot;debug&quot;; &quot;info&quot;; &quot;notice&quot;; &quot;warning&quot;; &quot;error&quot;; &quot;critical&quot;; &quot;alert&quot;; &quot;emergency&quot;</span></span>
<span class="line">                    <span class="token key atrule">bubble</span><span class="token punctuation">:</span>               <span class="token boolean important">true</span></span>
<span class="line">        <span class="token key atrule">loggers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">logger</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">name</span><span class="token punctuation">:</span>                 <span class="token null important">~</span></span>
<span class="line">                <span class="token key atrule">version</span><span class="token punctuation">:</span>              <span class="token null important">~</span></span>
<span class="line">                <span class="token key atrule">provider</span><span class="token punctuation">:</span>             <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">        <span class="token key atrule">providers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">provider</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">type</span><span class="token punctuation">:</span>                 default <span class="token comment"># One of &quot;default&quot;; &quot;noop&quot;, Required</span></span>
<span class="line">                <span class="token key atrule">processor</span><span class="token punctuation">:</span>            <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">processors</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">processor</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">type</span><span class="token punctuation">:</span>                 simple <span class="token comment"># One of &quot;multi&quot;; &quot;noop&quot;; &quot;simple&quot;, Required</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># Required if processor type is multi</span></span>
<span class="line">                <span class="token key atrule">processors</span><span class="token punctuation">:</span>           <span class="token punctuation">[</span><span class="token punctuation">]</span></span>
<span class="line"></span>
<span class="line">                <span class="token comment"># Required if processor type is simple</span></span>
<span class="line">                <span class="token key atrule">exporter</span><span class="token punctuation">:</span>             <span class="token null important">~</span></span>
<span class="line">        <span class="token key atrule">exporters</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment"># Prototype</span></span>
<span class="line">            <span class="token key atrule">exporter</span><span class="token punctuation">:</span></span>
<span class="line">                <span class="token key atrule">dsn</span><span class="token punctuation">:</span>                  <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                <span class="token key atrule">options</span><span class="token punctuation">:</span></span>
<span class="line">                    <span class="token key atrule">format</span><span class="token punctuation">:</span>               json <span class="token comment"># One of &quot;json&quot;; &quot;ndjson&quot;; &quot;gprc&quot;; &quot;protobuf&quot;</span></span>
<span class="line">                    <span class="token key atrule">compression</span><span class="token punctuation">:</span>          none <span class="token comment"># One of &quot;gzip&quot;; &quot;none&quot;</span></span>
<span class="line">                    <span class="token key atrule">headers</span><span class="token punctuation">:</span></span>
<span class="line"></span>
<span class="line">                        <span class="token comment"># Prototype</span></span>
<span class="line">                        <span class="token punctuation">-</span></span>
<span class="line">                            <span class="token key atrule">name</span><span class="token punctuation">:</span>                 <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                            <span class="token key atrule">value</span><span class="token punctuation">:</span>                <span class="token null important">~</span> <span class="token comment"># Required</span></span>
<span class="line">                    <span class="token key atrule">timeout</span><span class="token punctuation">:</span>              <span class="token number">0.1</span></span>
<span class="line">                    <span class="token key atrule">retry</span><span class="token punctuation">:</span>                <span class="token number">100</span></span>
<span class="line">                    <span class="token key atrule">max</span><span class="token punctuation">:</span>                  <span class="token number">3</span></span>
<span class="line">                    <span class="token key atrule">ca</span><span class="token punctuation">:</span>                   <span class="token null important">~</span></span>
<span class="line">                    <span class="token key atrule">cert</span><span class="token punctuation">:</span>                 <span class="token null important">~</span></span>
<span class="line">                    <span class="token key atrule">key</span><span class="token punctuation">:</span>                  <span class="token null important">~</span></span>
<span class="line"></span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div></div></div><blockquote><p><strong>Note</strong>: The configuration reference is generated from the bundle&#39;s source code. It is always up-to-date with the latest version of the bundle.</p></blockquote>`,6)]))}const o=s(p,[["render",t],["__file","configuration.html.vue"]]),u=JSON.parse('{"path":"/user-guide/configuration.html","title":"Configuration","lang":"en-US","frontmatter":{},"headers":[{"level":2,"title":"Full configuration reference","slug":"full-configuration-reference","link":"#full-configuration-reference","children":[]}],"filePathRelative":"user-guide/configuration.md","git":{"createdTime":1711217451000,"updatedTime":1711386927000,"contributors":[{"name":"Gaël Reyrol","username":"Gaël Reyrol","email":"me@gaelreyrol.dev","commits":2,"url":"https://github.com/Gaël Reyrol"}]}}');export{o as comp,u as data};

<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum OtlpExporterCompressionEnum: string
{
    case None = 'none';
    case Gzip = 'gzip';
}

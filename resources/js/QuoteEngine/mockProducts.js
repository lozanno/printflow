export const products = [
  {
    id: "business_cards",
    name: "Tarjetas",
    description: "Tarjetas de presentación.",
    fields: [
      { id: "quantity", label: "Cantidad", type: "choice", options: ["100", "250", "500", "1000"] },
      { id: "size", label: "Tamaño", type: "choice", options: ["90 × 50 mm", "90 × 55 mm", "85 × 55 mm"] },
      { id: "sides", label: "Impresión", type: "choice", options: ["Solo frente", "Frente y vuelta"] },
      { id: "finish", label: "Acabado", type: "choice", options: ["Sin laminado", "Laminado mate", "Laminado brillante"] },
    ],
  },
  {
    id: "flyers",
    name: "Volantes",
    description: "Volantes para promociones.",
    fields: [
      { id: "quantity", label: "Cantidad", type: "choice", options: ["100", "250", "500", "1000"] },
      { id: "size", label: "Tamaño", type: "choice", options: ["Media carta", "Cuarto de carta"] },
      { id: "sides", label: "Impresión", type: "choice", options: ["Solo frente", "Frente y vuelta"] },
    ],
  },
  {
    id: "banners",
    name: "Lonas",
    description: "Lonas impresas.",
    fields: [
      { id: "width", label: "Ancho en metros", type: "number", placeholder: "Ej. 2.50" },
      { id: "height", label: "Alto en metros", type: "number", placeholder: "Ej. 1.20" },
      { id: "finish", label: "Acabado", type: "choice", options: ["Sin acabado", "Bastilla y ojillos"] },
    ],
  },
];

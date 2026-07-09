import { useMemo, useState } from "react";
import { products } from "./mockProducts";

export default function ProductConfigurator() {
  const [selectedProductId, setSelectedProductId] = useState(products[0].id);
  const [answers, setAnswers] = useState({});

  const selectedProduct = useMemo(
    () => products.find((product) => product.id === selectedProductId),
    [selectedProductId]
  );

  function selectProduct(productId) {
    setSelectedProductId(productId);
    setAnswers({});
  }

  function updateAnswer(fieldId, value) {
    setAnswers((current) => ({ ...current, [fieldId]: value }));
  }

  return (
    <div className="min-h-screen bg-zinc-100 px-6 py-10">
      <div className="mx-auto max-w-5xl">
        <p className="text-sm font-semibold uppercase tracking-wide text-zinc-500">
          Quote Engine
        </p>

        <h1 className="text-4xl font-bold tracking-tight text-zinc-900">
          ¿Qué deseas imprimir?
        </h1>

        <p className="mt-2 text-zinc-600">
          Configura tu producto y obtén una cotización rápida.
        </p>

        <div className="mt-8 grid gap-4 md:grid-cols-3">
          {products.map((product) => (
            <button
              key={product.id}
              onClick={() => selectProduct(product.id)}
              className={`rounded-2xl border p-5 text-left shadow-sm transition ${
                selectedProductId === product.id
                  ? "border-zinc-900 bg-white"
                  : "border-zinc-200 bg-white hover:border-zinc-400"
              }`}
            >
              <h2 className="text-xl font-semibold text-zinc-900">{product.name}</h2>
              <p className="mt-2 text-sm text-zinc-600">{product.description}</p>
            </button>
          ))}
        </div>

        <div className="mt-8 grid gap-8 lg:grid-cols-[1fr_340px]">
          <div className="rounded-2xl bg-white p-6 shadow-sm">
            <h2 className="text-2xl font-bold text-zinc-900">
              {selectedProduct.name}
            </h2>

            <div className="mt-6 space-y-7">
              {selectedProduct.fields.map((field) => (
                <div key={field.id}>
                  <label className="block text-sm font-semibold text-zinc-800">
                    {field.label}
                  </label>

                  {field.type === "choice" && (
                    <div className="mt-3 flex flex-wrap gap-3">
                      {field.options.map((option) => (
                        <button
                          key={option}
                          onClick={() => updateAnswer(field.id, option)}
                          className={`rounded-xl border px-4 py-3 text-sm font-medium transition ${
                            answers[field.id] === option
                              ? "border-zinc-900 bg-zinc-900 text-white"
                              : "border-zinc-200 bg-white text-zinc-800 hover:border-zinc-400"
                          }`}
                        >
                          {option}
                        </button>
                      ))}
                    </div>
                  )}

                  {field.type === "number" && (
                    <input
                      type="number"
                      step="0.01"
                      placeholder={field.placeholder}
                      value={answers[field.id] || ""}
                      onChange={(event) => updateAnswer(field.id, event.target.value)}
                      className="mt-3 w-full rounded-xl border border-zinc-300 px-4 py-3 outline-none focus:border-zinc-900"
                    />
                  )}
                </div>
              ))}
            </div>
          </div>

          <div className="rounded-2xl bg-zinc-900 p-6 text-white shadow-sm">
            <h3 className="text-xl font-bold">Resumen</h3>

            <div className="mt-6 space-y-4">
              <div>
                <p className="text-xs uppercase text-zinc-400">Producto</p>
                <p className="font-semibold">{selectedProduct.name}</p>
              </div>

              {selectedProduct.fields.map((field) => (
                <div key={field.id}>
                  <p className="text-xs uppercase text-zinc-400">{field.label}</p>
                  <p className="font-semibold">{answers[field.id] || "Pendiente"}</p>
                </div>
              ))}
            </div>

            <div className="mt-8 rounded-xl bg-zinc-800 p-4">
              <p className="text-xs uppercase text-zinc-400">Precio demo</p>
              <p className="text-3xl font-bold">$0.00</p>
            </div>

            <button className="mt-4 w-full rounded-xl bg-white px-4 py-3 font-bold text-zinc-900">
              Cotizar
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

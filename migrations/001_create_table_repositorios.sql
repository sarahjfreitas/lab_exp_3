CREATE TABLE [dbo].[repositorios]
(
	[id_repositorio] UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID(),
	[name_with_owner] NVARCHAR(1000) NOT NULL,
	[status] INT NOT NULL DEFAULT 0,
	[total] INT NOT NULL,
	[processed] INT NOT NULL DEFAULT 0,

	CONSTRAINT [PK_dbo__repositorios__id_repositorio] PRIMARY KEY CLUSTERED
	(
		[id_repositorio] ASC
	)
	WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
)
ON [PRIMARY]
GO